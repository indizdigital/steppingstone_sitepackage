<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Form\Mvc\Persistence\Event\AfterFormDefinitionLoadedEvent;

#[AsEventListener(
    identifier: 'indiz-event-select-options'
)]
final class EventSelectListener
{
    public function __construct(
        private readonly EventSelectOptionsProvider $provider
    ) {
    }


    public function __invoke(
        AfterFormDefinitionLoadedEvent $event
    ): void {

        $formDefinition = $event->getFormDefinition();

        $options = $this->provider->getOptions();

        $this->replaceEventOptions(
            $formDefinition,
            $options
        );

        $event->setFormDefinition($formDefinition);
    }


    private function replaceEventOptions(
        array &$formDefinition,
        array $options
    ): void {
        if (!isset($formDefinition['renderables'])) {
            return;
        }

        foreach ($formDefinition['renderables'] as &$renderable) {

            if (
                ($renderable['identifier'] ?? null) === 'venue'
                &&
                ($renderable['type'] ?? null) === 'SingleSelect'
            ) {
                $renderable['properties']['options'] = $options;

                return;
            }

            if (
                isset($renderable['renderables'])
                &&
                is_array($renderable['renderables'])
            ) {
                $this->replaceEventOptions(
                    $renderable,
                    $options
                );
            }
        }
    }
}