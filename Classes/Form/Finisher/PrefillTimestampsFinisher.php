<?php
declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class PrefillTimestampsFinisher extends AbstractFinisher
{
    protected function executeInternal(): void
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zurich')))->getTimestamp();
        $this->finisherContext->getFinisherVariableProvider()->add('PrefillTimestamps', 'crdate', $now);
        $this->finisherContext->getFinisherVariableProvider()->add('PrefillTimestamps', 'tstamp', $now);
    }
}
