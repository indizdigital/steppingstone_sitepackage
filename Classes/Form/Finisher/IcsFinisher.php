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

        //if abmeldung omit the ics file
        if(isset($formValues["numberofpeople"]) && $formValues["numberofpeople"] == "decline"){
            return;
        }
        
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
            'BEGIN:VTIMEZONE',
            'TZID:Europe/Zurich',
            'BEGIN:DAYLIGHT',
            'TZOFFSETFROM:+0100',
            'TZOFFSETTO:+0200',
            'TZNAME:CEST',
            'DTSTART:19700329T020000',
            'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU',
            'END:DAYLIGHT',
            'BEGIN:STANDARD',
            'TZOFFSETFROM:+0200',
            'TZOFFSETTO:+0100',
            'TZNAME:CET',
            'DTSTART:19701025T030000',
            'RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU',
            'END:STANDARD',
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'UID:' . $event['uid'] . '@stepping-stone.ch',
            'DTSTAMP:' . $this->formatDate(time()),
            'DTSTART;TZID=Europe/Zurich:' . $this->formatLocalDate((int)$event['startdate']),
            'DTEND;TZID=Europe/Zurich:' . $this->formatLocalDate((int)$event['enddate']),
            'SUMMARY:' . $this->escape($event['title']),
            'LOCATION:' . $this->escape($event['location']),
            'DESCRIPTION:' . $this->escape($event['description']),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }


    // $timestamp here is a true "now" epoch (time()), so it's genuinely UTC.
    private function formatDate(int $timestamp): string
    {
        return gmdate(
            'Ymd\THis\Z',
            $timestamp
        );
    }


    // $event['startdate']/['enddate'] are naive timestamps whose digits, read
    // as UTC, already give the intended Europe/Zurich wall-clock time (see
    // EventSelectOptionsProvider, which relies on the same behaviour). So we
    // extract the raw digits via gmdate() and tag them with TZID instead of
    // relabeling them as real UTC.
    private function formatLocalDate(int $timestamp): string
    {
        return gmdate(
            'Ymd\THis',
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