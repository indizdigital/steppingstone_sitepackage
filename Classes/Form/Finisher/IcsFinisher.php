<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form\Finisher;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

final class IcsFinisher extends AbstractFinisher
{
    protected function executeInternal(): void
    {
        $formValues = $this->finisherContext
            ->getFormRuntime()
            ->getFormState()
            ->getFormValues();


        // Deine Select-Option "venue" enthält aktuell die Event-UID
        $eventUid = (int)($formValues['venue'] ?? 0);


        if (!$eventUid) {
            return;
        }


        $event = $this->getEvent($eventUid);


        if (!$event) {
            return;
        }


        $ics = $this->createIcs($event);


        $fileName = GeneralUtility::tempnam(
            'event',
            '.ics'
        );


        file_put_contents(
            $fileName,
            $ics
        );


        // Übergabe an nachfolgende Finisher
        $this->finisherContext
        ->getFinisherVariableProvider()
        ->add(
            'IcsFinisher',
            'attachments',
            [
                [
                    'file' => $fileName,
                    'filename' => 'event.ics'
                ]
            ]
        );
    }


    private function getEvent(int $uid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(
            ConnectionPool::class
        )->getQueryBuilderForTable('tx_ndz_event');


        return $queryBuilder
            ->select('*')
            ->from('tx_ndz_event')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid)
                )
            )
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }


    private function createIcs(array $event): string
    {
        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//stepping stone//Event//DE',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:' . $event['uid'] . '@stepping-stone.ch',
            'DTSTAMP:' . $this->formatDate(time()),
            'DTSTART:' . $this->formatDate((int)$event['startdate']),
            'DTEND:' . $this->formatDate((int)$event['enddate']),
            'SUMMARY:' . $this->escape($event['title']),
            'LOCATION:' . $this->escape($event['location']),
            'DESCRIPTION:' . $this->escape($event['description']),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }


    private function formatDate(int $timestamp): string
    {
        return gmdate(
            'Ymd\THis\Z',
            $timestamp
        );
    }


    private function escape(string $value): string
    {
        return str_replace(
            [
                '\\',
                ';',
                ',',
                "\r\n",
                "\n",
            ],
            [
                '\\\\',
                '\;',
                '\,',
                '\n',
                '\n',
            ],
            $value
        );
    }
}