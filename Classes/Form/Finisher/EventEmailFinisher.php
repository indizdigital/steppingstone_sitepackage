<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        $email->assign('event', $this->getEventTitle($formRuntime));

        return $email;
    }

    /**
     * Looks up the chosen event's title independently of IcsFinisher, since
     * that one skips its own lookup entirely when the visitor declined
     * ("numberofpeople" == "decline") - but the "no_attendance" email text
     * needs the event title in exactly that case too.
     */
    private function getEventTitle(FormRuntime $formRuntime): string
    {
        $eventUid = (int)($formRuntime->getFormState()->getFormValues()['venue'] ?? 0);
        if (!$eventUid) {
            return '';
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_ndz_event');

        $event = $queryBuilder
            ->select('*')
            ->from('tx_ndz_event')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($eventUid, \Doctrine\DBAL\ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        if (!$event) {
            return '';
        }

        // The query above always returns the default-language record;
        // the overlay swaps in translatable fields (incl. title) for the
        // language the visitor actually submitted the form in.
        $event = GeneralUtility::makeInstance(PageRepository::class)
            ->getLanguageOverlay('tx_ndz_event', $event);

        return (string)($event['title'] ?? '');
    }
}