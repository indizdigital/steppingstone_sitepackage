<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\EventListener;

use IndizDigitalGmbh\SteppingStoneSitePackage\Form\Persistence\DynamicFormColumnsRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

/**
 * Registers a full TCA definition for every per-form submissions table that
 * SchemaSyncingFormPersistenceManager created (tx_steppingstonesitepackage_form_<formIdentifier>).
 * These tables have no static Configuration/TCA file of their own, since
 * their names are only known once a form using a SaveToDatabase(WithPid)
 * finisher is actually saved.
 *
 * Reads column/label information from DynamicFormColumnsRepository (a plain
 * file on disk) instead of the database, because TCA compilation happens in
 * a bootstrap phase where database access is not available.
 */
#[AsEventListener]
final class ExtendFormSubmissionsTca
{
    private const TCA_CONFIG_BY_ELEMENT_TYPE = [
        'Textarea' => ['type' => 'text', 'rows' => 5, 'readOnly' => true],
        'Checkbox' => ['type' => 'check', 'readOnly' => true],
    ];

    private const DEFAULT_TCA_CONFIG = ['type' => 'input', 'readOnly' => true];

    public function __construct(
        private readonly DynamicFormColumnsRepository $dynamicFormColumnsRepository,
    ) {}

    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        try {
            $tca = $event->getTca();
            foreach ($this->dynamicFormColumnsRepository->read() as $table => $formsByIdentifier) {
                if (isset($tca[$table]) || $formsByIdentifier === []) {
                    continue;
                }
                $formIdentifier = (string)array_key_first($formsByIdentifier);
                $tca[$table] = $this->buildTableTca($formIdentifier, $formsByIdentifier[$formIdentifier]);
            }
            $event->setTca($tca);
        } catch (\Throwable) {
            // Never break TCA compilation because of this best-effort enhancement.
        }
    }

    /**
     * @param array{formLabel?: string, formType?: string, columns?: array<string, array{label: string, type: string, isRecordLabel?: bool}>} $form
     */
    private function buildTableTca(string $formIdentifier, array $form): array
    {
        $columnDescriptions = $form['columns'] ?? [];
        $formLabel = (string)($form['formLabel'] ?? $formIdentifier);
        $formType = (string)($form['formType'] ?? 'Form');

        $columns = [];
        $labelColumn = null;
        foreach ($columnDescriptions as $columnName => $description) {
            $columns[$columnName] = [
                'label' => $description['label'],
                'config' => self::TCA_CONFIG_BY_ELEMENT_TYPE[$description['type']] ?? self::DEFAULT_TCA_CONFIG,
            ];
            if ($labelColumn === null && ($description['isRecordLabel'] ?? false) === true) {
                $labelColumn = $columnName;
            }
        }

        return [
            'ctrl' => [
                'title' => sprintf('%s – %s', $formType, $formLabel),
                'label' => $labelColumn ?? (string)array_key_first($columns),
                'crdate' => 'crdate',
                'tstamp' => 'tstamp',
                'delete' => 'deleted',
                'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/content/content-form.svg',
            ],
            'columns' => $columns,
            'types' => [
                '0' => ['showitem' => implode(', ', array_keys($columns))],
            ],
        ];
    }
}
