<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\UserFunction;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ActiveSlideItems
{
    public function getItems(array &$parameters): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        // 1. Kategorien sortiert nach sorting laden
        $categoryQb = $connectionPool->getQueryBuilderForTable('tx_ndz_onboardingcategory');
        $categories = $categoryQb
            ->select('uid', 'steps')
            ->from('tx_ndz_onboardingcategory')
            ->where($categoryQb->expr()->eq('deleted', 0))
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        // 2. Step-UIDs in korrekter Reihenfolge sammeln (Kategorie-Reihenfolge → Step-Reihenfolge)
        $orderedStepUids = [];
        foreach ($categories as $category) {
            if (empty($category['steps'])) {
                continue;
            }
            foreach (array_filter(explode(',', (string)$category['steps'])) as $stepUid) {
                $stepUid = (int)$stepUid;
                if ($stepUid > 0 && !in_array($stepUid, $orderedStepUids, true)) {
                    $orderedStepUids[] = $stepUid;
                }
            }
        }

        if (empty($orderedStepUids)) {
            return;
        }

        // 3. Alle Steps in einer Query laden
        $stepQb = $connectionPool->getQueryBuilderForTable('tx_ndz_onboardingstep');
        $steps = $stepQb
            ->select('uid', 'title')
            ->from('tx_ndz_onboardingstep')
            ->where(
                $stepQb->expr()->in('uid', $orderedStepUids),
                $stepQb->expr()->eq('deleted', 0)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        // 4. Nach UID indexieren für schnellen Zugriff
        $stepsIndexed = array_column($steps, null, 'uid');

        // 5. Items in der berechneten Reihenfolge aufbauen
        $index = 1;
        foreach ($orderedStepUids as $uid) {
            if (isset($stepsIndexed[$uid])) {
                $parameters['items'][] = [
                    'label' => $stepsIndexed[$uid]['title'],
                    'value' => $index,
                ];
                $index++;
            }
        }
    }
}
