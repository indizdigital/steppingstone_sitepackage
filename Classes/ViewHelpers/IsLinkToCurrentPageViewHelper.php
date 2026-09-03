<?php
declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers;

use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class IsLinkToCurrentPageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('link', 'string', 'Linkparameter (typolink)', true);
        $this->registerArgument('currentPageUid', 'int', 'UID der aktuell dargestellten Seite', true);
    }

    public function render(): bool
    {
        $link = trim((string)$this->arguments['link']);
        if ($link === '') {
            return false;
        }
        if (str_starts_with($link, '#')) {
            return true;
        }

        try {
            $resolved = GeneralUtility::makeInstance(LinkService::class)->resolve($link);
        } catch (\Exception) {
            return false;
        }

        if (($resolved['type'] ?? '') !== 'page') {
            return false;
        }

        return (int)($resolved['pageuid'] ?? 0) === (int)$this->arguments['currentPageUid'];
    }
}
