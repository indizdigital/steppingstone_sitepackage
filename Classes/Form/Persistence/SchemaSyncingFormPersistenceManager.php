<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Persistence;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Form\Enum\SortDirection;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

/**
 * Decorates the core FormPersistenceManager to keep the database schema of
 * SaveToDatabase(WithPid) finishers in sync with the form's fields.
 *
 * Every form using such a finisher gets its own table, named
 * tx_steppingstonesitepackage_form_<formIdentifier>, so that different forms
 * never share column names. Every storable form element gets an automatic
 * "mapOnDatabaseColumn" mapping (unless one already exists or the element
 * opts out), and the table plus any missing columns are created on save.
 * Existing tables/columns are never altered or dropped.
 */
final class SchemaSyncingFormPersistenceManager implements FormPersistenceManagerInterface
{
    private const EXCLUDED_ELEMENT_TYPES = [
        'Password',
        'AdvancedPassword',
        'Honeypot',
        'GridRow',
        'GridColumn',
        'Fieldset',
        'Page',
        'SummaryPage',
        'StaticText',
        'ContentElement',
        'FileUpload',
        'ImageUpload',
    ];

    private const DEFAULT_COLUMN_DEFINITION = 'varchar(255) DEFAULT NULL';

    private const COLUMN_DEFINITIONS_BY_ELEMENT_TYPE = [
        'Textarea' => 'text',
        'Checkbox' => 'tinyint(1) DEFAULT NULL',
    ];

    /**
     * Finisher identifiers backed by TYPO3\CMS\Form\Domain\Finishers\SaveToDatabaseFinisher.
     * Only the single-operation option shape (options.table / options.elements) is supported.
     */
    private const DATABASE_FINISHER_IDENTIFIERS = [
        'SaveToDatabase',
        'SaveToDatabaseWithPid',
    ];

    private const TABLE_PREFIX = 'tx_steppingstonesitepackage_form_';

    private const MAX_TABLE_NAME_LENGTH = 64;

    /**
     * Option key a previous version of this class used to inject a static
     * "form_identifier" value into database finishers. That column no longer
     * exists now that every form gets its own table, so this leftover must
     * be stripped from forms saved with that older version.
     */
    private const LEGACY_FORM_IDENTIFIER_COLUMN = 'form_identifier';

    public function __construct(
        private readonly FormPersistenceManagerInterface $decoratedFormPersistenceManager,
        private readonly SchemaMigrator $schemaMigrator,
        private readonly DynamicFormColumnsRepository $dynamicFormColumnsRepository,
        private readonly CacheManager $cacheManager,
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function load(string $persistenceIdentifier, array $formSettings, array $typoScriptSettings): array
    {
        $formDefinition = $this->decoratedFormPersistenceManager->load($persistenceIdentifier, $formSettings, $typoScriptSettings);
        return $this->removeLegacyFormIdentifierColumnMapping($formDefinition);
    }

    public function save(string $persistenceIdentifier, array $formDefinition, array $formSettings): void
    {
        $formDefinition = $this->addMissingDatabaseColumnMappings($formDefinition);
        $this->decoratedFormPersistenceManager->save($persistenceIdentifier, $formDefinition, $formSettings);
        $this->synchronizeDatabaseSchema($formDefinition);
        $this->pruneStaleDynamicTables();
        // Flush unconditionally, not just when the DB schema actually changed:
        // label/type metadata read by ExtendFormSubmissionsTca can change too.
        $this->cacheManager->getCache('core')->flush();
    }

    public function delete(string $persistenceIdentifier, array $formSettings): void
    {
        $this->decoratedFormPersistenceManager->delete($persistenceIdentifier, $formSettings);
    }

    public function listForms(array $formSettings, string $orderField = '', ?SortDirection $orderDirection = null): array
    {
        $this->pruneStaleDynamicTables();
        return $this->decoratedFormPersistenceManager->listForms($formSettings, $orderField, $orderDirection);
    }

    public function hasForms(array $formSettings): bool
    {
        return $this->decoratedFormPersistenceManager->hasForms($formSettings);
    }

    public function getAccessibleFormStorageFolders(array $formSettings): array
    {
        return $this->decoratedFormPersistenceManager->getAccessibleFormStorageFolders($formSettings);
    }

    public function getAccessibleExtensionFolders(array $formSettings): array
    {
        return $this->decoratedFormPersistenceManager->getAccessibleExtensionFolders($formSettings);
    }

    public function getUniquePersistenceIdentifier(string $formIdentifier, string $savePath, array $formSettings): string
    {
        return $this->decoratedFormPersistenceManager->getUniquePersistenceIdentifier($formIdentifier, $savePath, $formSettings);
    }

    public function getUniqueIdentifier(array $formSettings, string $identifier): string
    {
        return $this->decoratedFormPersistenceManager->getUniqueIdentifier($formSettings, $identifier);
    }

    public function isAllowedPersistencePath(string $persistencePath, array $formSettings): bool
    {
        return $this->decoratedFormPersistenceManager->isAllowedPersistencePath($persistencePath, $formSettings);
    }

    public function hasValidFileExtension(string $fileName): bool
    {
        return $this->decoratedFormPersistenceManager->hasValidFileExtension($fileName);
    }

    private function addMissingDatabaseColumnMappings(array $formDefinition): array
    {
        $formDefinition = $this->removeLegacyFormIdentifierColumnMapping($formDefinition);

        $storableElements = $this->getStorableElements($formDefinition['renderables'] ?? []);
        $formIdentifier = (string)($formDefinition['identifier'] ?? '');
        $formLabel = (string)($formDefinition['label'] ?? $formIdentifier);
        $formType = (string)($formDefinition['type'] ?? 'Form');
        $table = $this->computeTableName($formIdentifier);

        foreach ($formDefinition['finishers'] ?? [] as $finisherIndex => $finisher) {
            if (!in_array($finisher['identifier'] ?? '', self::DATABASE_FINISHER_IDENTIFIERS, true)) {
                continue;
            }

            $elementsConfiguration = $finisher['options']['elements'] ?? [];
            $usedColumnNames = array_column($elementsConfiguration, 'mapOnDatabaseColumn');
            foreach ($storableElements as $elementIdentifier => $element) {
                if (isset($elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn'])) {
                    continue;
                }
                $columnName = $this->sanitizeColumnName($elementIdentifier);
                if ($columnName === '' || in_array($columnName, $usedColumnNames, true)) {
                    continue;
                }
                $elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn'] = $columnName;
                $usedColumnNames[] = $columnName;
            }

            // Drop mappings for elements that no longer exist on the form (deleted or now excluded);
            // their database columns are left untouched, only the now-dead mapping is removed.
            $elementsConfiguration = array_intersect_key($elementsConfiguration, $storableElements);

            $formDefinition['finishers'][$finisherIndex]['options']['elements'] = $elementsConfiguration;
            $formDefinition['finishers'][$finisherIndex]['options']['table'] = $table;

            if ($this->isValidSqlIdentifier($table)) {
                $columnDescriptions = $this->buildColumnDescriptions($elementsConfiguration, $storableElements);
                $this->dynamicFormColumnsRepository->set($table, $formIdentifier, $formLabel, $formType, $columnDescriptions);
            }
        }

        return $formDefinition;
    }

    /**
     * Removes dynamic-table registrations whose table was deleted outside of
     * this mechanism (e.g. manually via phpMyAdmin). Without this, the
     * backend keeps trying to list/query a table that no longer exists and
     * fails with a TableNotFoundException.
     */
    private function pruneStaleDynamicTables(): void
    {
        $registeredTables = $this->dynamicFormColumnsRepository->read();
        if ($registeredTables === []) {
            return;
        }

        $existingTables = $this->connectionPool->getConnectionForTable('pages')->createSchemaManager()->listTableNames();
        $cacheNeedsFlush = false;

        foreach (array_keys($registeredTables) as $table) {
            if (!in_array($table, $existingTables, true)) {
                $this->dynamicFormColumnsRepository->remove($table);
                $cacheNeedsFlush = true;
            }
        }

        if ($cacheNeedsFlush) {
            $this->cacheManager->getCache('core')->flush();
        }
    }

    /**
     * Strips the "form_identifier" static database column mapping that a
     * previous version of this class injected into database finishers. That
     * column doesn't exist on the per-form tables created by the current
     * version, so leaving this mapping in place makes the finisher fail with
     * "Unknown column 'form_identifier' in INSERT INTO" on already-saved forms.
     */
    private function removeLegacyFormIdentifierColumnMapping(array $formDefinition): array
    {
        foreach ($formDefinition['finishers'] ?? [] as $finisherIndex => $finisher) {
            if (!in_array($finisher['identifier'] ?? '', self::DATABASE_FINISHER_IDENTIFIERS, true)) {
                continue;
            }

            unset($formDefinition['finishers'][$finisherIndex]['options']['databaseColumnMappings'][self::LEGACY_FORM_IDENTIFIER_COLUMN]);

            if (($formDefinition['finishers'][$finisherIndex]['options']['databaseColumnMappings'] ?? null) === []) {
                unset($formDefinition['finishers'][$finisherIndex]['options']['databaseColumnMappings']);
            }
        }

        return $formDefinition;
    }

    /**
     * @return array<string, array{label: string, type: string, isRecordLabel: bool}> column => description
     */
    private function buildColumnDescriptions(array $elementsConfiguration, array $storableElements): array
    {
        $columnDescriptions = [];
        foreach ($elementsConfiguration as $elementIdentifier => $elementConfiguration) {
            $columnName = $elementConfiguration['mapOnDatabaseColumn'] ?? '';
            if ($columnName === '' || !isset($storableElements[$elementIdentifier])) {
                continue;
            }
            $columnDescriptions[$columnName] = [
                'label' => $storableElements[$elementIdentifier]['label'],
                'type' => $storableElements[$elementIdentifier]['type'],
                'isRecordLabel' => $storableElements[$elementIdentifier]['isRecordLabel'],
            ];
        }
        return $columnDescriptions;
    }

    private function synchronizeDatabaseSchema(array $formDefinition): void
    {
        $storableElements = $this->getStorableElements($formDefinition['renderables'] ?? []);
        $createTableStatements = [];

        foreach ($formDefinition['finishers'] ?? [] as $finisher) {
            if (!in_array($finisher['identifier'] ?? '', self::DATABASE_FINISHER_IDENTIFIERS, true)) {
                continue;
            }

            $table = $finisher['options']['table'] ?? '';
            if (!$this->isValidSqlIdentifier($table)) {
                continue;
            }

            $columnDefinitionsByName = [];
            foreach ($finisher['options']['elements'] ?? [] as $elementIdentifier => $elementConfiguration) {
                $columnName = $elementConfiguration['mapOnDatabaseColumn'] ?? '';
                if (!$this->isValidSqlIdentifier($columnName)) {
                    continue;
                }
                $elementType = $storableElements[$elementIdentifier]['type'] ?? '';
                $columnDefinitionsByName[$columnName] = self::COLUMN_DEFINITIONS_BY_ELEMENT_TYPE[$elementType]
                    ?? self::DEFAULT_COLUMN_DEFINITION;
            }

            $createTableStatements[$table] = $this->buildCreateTableStatement($table, $columnDefinitionsByName);
        }

        if ($createTableStatements === [] || !$this->hasMissingFields($createTableStatements)) {
            return;
        }

        $this->schemaMigrator->install(array_values($createTableStatements), true);
    }

    private function hasMissingFields(array $createTableStatements): bool
    {
        $updateSuggestions = $this->schemaMigrator->getUpdateSuggestions(array_values($createTableStatements));
        foreach ($updateSuggestions as $connectionSuggestions) {
            if (!empty($connectionSuggestions['add']) || !empty($connectionSuggestions['create_table'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<string, array{type: string, label: string, isRecordLabel: bool}> elementIdentifier => element
     */
    private function getStorableElements(array $renderables): array
    {
        $elements = [];
        foreach ($renderables as $renderable) {
            if (!empty($renderable['renderables'])) {
                $elements += $this->getStorableElements($renderable['renderables']);
                continue;
            }

            $type = $renderable['type'] ?? '';
            $identifier = $renderable['identifier'] ?? '';

            if ($identifier === '' || $type === '' || in_array($type, self::EXCLUDED_ELEMENT_TYPES, true)) {
                continue;
            }

            if (($renderable['properties']['excludeFromDatabaseStorage'] ?? false) === true) {
                continue;
            }

            $elements[$identifier] = [
                'type' => $type,
                'label' => (string)($renderable['label'] ?? $identifier),
                'isRecordLabel' => ($renderable['properties']['useAsRecordLabel'] ?? false) === true,
            ];
        }

        return $elements;
    }

    /**
     * Every dynamically managed table gets the standard TYPO3 base fields
     * (uid/pid/tstamp/crdate/deleted), since these tables have no static
     * ext_tables.sql entry - their names are only known once a form using a
     * database finisher is actually saved.
     */
    private function buildCreateTableStatement(string $table, array $columnDefinitionsByName): string
    {
        $fields = [
            'uid int(11) unsigned NOT NULL auto_increment',
            "pid int(11) unsigned DEFAULT '0' NOT NULL",
            "tstamp int(11) unsigned DEFAULT '0' NOT NULL",
            "crdate int(11) unsigned DEFAULT '0' NOT NULL",
            "deleted smallint(5) unsigned DEFAULT '0' NOT NULL",
        ];

        foreach ($columnDefinitionsByName as $columnName => $definition) {
            $fields[] = sprintf('%s %s', $columnName, $definition);
        }

        $fields[] = 'PRIMARY KEY (uid)';
        $fields[] = 'KEY parent (pid)';

        return sprintf("CREATE TABLE %s (\n%s\n);", $table, implode(",\n", $fields));
    }

    private function isValidSqlIdentifier(string $identifier): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $identifier) === 1;
    }

    /**
     * Converts a form identifier into a safe, length-limited database table
     * name of the form tx_steppingstonesitepackage_form_<formIdentifier>.
     */
    private function computeTableName(string $formIdentifier): string
    {
        return substr(self::TABLE_PREFIX . $this->sanitizeIdentifierFragment($formIdentifier), 0, self::MAX_TABLE_NAME_LENGTH);
    }

    /**
     * Converts a form element identifier (which may contain characters that
     * are not allowed in an unquoted SQL identifier, e.g. hyphens) into a
     * safe database column name.
     */
    private function sanitizeColumnName(string $identifier): string
    {
        $columnName = $this->sanitizeIdentifierFragment($identifier);
        if (!$this->isValidSqlIdentifier($columnName)) {
            return '';
        }
        return $columnName;
    }

    private function sanitizeIdentifierFragment(string $identifier): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $identifier) ?? '';
    }
}
