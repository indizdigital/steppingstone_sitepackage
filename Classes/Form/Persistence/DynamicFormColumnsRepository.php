<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Persistence;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Tracks, on the filesystem only (never the database), which columns were
 * dynamically added to which form-submission table, which form each column
 * belongs to, and which label/original form element type they should be
 * shown with in the backend.
 *
 * Written by SchemaSyncingFormPersistenceManager during a normal backend
 * request. Read by ExtendFormSubmissionsTca during TCA compilation, where
 * database access is not available - this class must never depend on
 * anything that touches the database.
 */
final class DynamicFormColumnsRepository
{
    /**
     * @return array<string, array<string, array{formLabel: string, formType: string, columns: array<string, array{label: string, type: string}>}>>
     *         table => [formIdentifier => [formLabel, formType, columns => [column => description]]]
     */
    public function read(): array
    {
        $filePath = $this->getFilePath();
        if (!is_file($filePath)) {
            return [];
        }

        $data = json_decode((string)file_get_contents($filePath), true);
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, array{label: string, type: string}> $columnDescriptions column => description
     */
    public function set(string $table, string $formIdentifier, string $formLabel, string $formType, array $columnDescriptions): void
    {
        $data = $this->read();
        $data[$table][$formIdentifier]['formLabel'] = $formLabel;
        $data[$table][$formIdentifier]['formType'] = $formType;
        $data[$table][$formIdentifier]['columns'] = array_merge(
            $data[$table][$formIdentifier]['columns'] ?? [],
            $columnDescriptions
        );

        $filePath = $this->getFilePath();
        GeneralUtility::mkdir_deep(dirname($filePath));
        GeneralUtility::writeFile($filePath, (string)json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function remove(string $table): void
    {
        $data = $this->read();
        if (!isset($data[$table])) {
            return;
        }

        unset($data[$table]);

        $filePath = $this->getFilePath();
        GeneralUtility::mkdir_deep(dirname($filePath));
        GeneralUtility::writeFile($filePath, (string)json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getFilePath(): string
    {
        return Environment::getVarPath() . '/stepping_stone_site_package/dynamic-form-columns.json';
    }
}
