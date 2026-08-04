<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Core\Mail\FluidEmail;

final class EventEmailFinisher extends EmailFinisher
{
    protected function initializeFluidEmail(FormRuntime $formRuntime): FluidEmail
    {
        $email = parent::initializeFluidEmail($formRuntime);

        $attachments = $this->finisherContext
            ->getFinisherVariableProvider()
            ->get('IcsFinisher', 'attachments');

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $email->attachFromPath(
                    $attachment['file'],
                    $attachment['filename']
                );
            }
        }

        return $email;
    }
}