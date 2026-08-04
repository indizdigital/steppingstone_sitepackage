<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Form;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class EventSelectOptionsProvider
{
    public function getOptions(): array
    {

        $pageUid = (int)($GLOBALS['TSFE']->id ?? 0);

        if (!$pageUid) {
            return [];
        }


        $pageRepository = GeneralUtility::makeInstance(
            PageRepository::class
        );

        $page = $pageRepository->getPage($pageUid);

        if (!$page) {
            return [];
        }


        /**
         * Content Block Relation Feld
         */
        $eventUids = $page['ndz_eventpage_event_select'] ?? [];


        if (!$eventUids) {
            return [];
        }


        return $this->getEventOptions($eventUids);
    }


    private function getEventOptions(array|string $eventUids): array
    {
        if (is_string($eventUids)) {
            $eventUids = GeneralUtility::intExplode(
                ',',
                $eventUids
            );
        }


        if ($eventUids === []) {
            return [];
        }


        $queryBuilder = GeneralUtility::makeInstance(
            ConnectionPool::class
        )->getQueryBuilderForTable('tx_ndz_event');


        $events = $queryBuilder
            ->select(
                'uid',
                'startdate',
                'location'
            )
            ->from('tx_ndz_event')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        $eventUids,
                        \Doctrine\DBAL\ArrayParameterType::INTEGER
                    )
                )
            )
            ->orderBy(
                'startdate',
                'ASC'
            )
            ->executeQuery()
            ->fetchAllAssociative();


        $options = [];


        foreach ($events as $event) {

            $date = new \DateTime(
                '@' . $event['startdate']
            );

            $date->setTimezone(
                new \DateTimeZone(
                    date_default_timezone_get()
                )
            );


            $formatter = new \IntlDateFormatter(
                'de_CH',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                'Europe/Zurich',
                \IntlDateFormatter::GREGORIAN,
                'EEEE, d. MMMM yyyy'
            );


            $options[(string)$event['uid']] =
                $event['location']
                . ', '
                . $formatter->format($date);
        }


        return $options;
    }
}