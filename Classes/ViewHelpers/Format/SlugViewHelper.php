<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers\Format;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SlugViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Der zu konvertierende String', false);
        $this->registerArgument('key', 'string', 'Übersetzungsschlüssel (alternativ zu value)', false);
        $this->registerArgument('extensionName', 'string', 'Extension-Name für die locallang (nur mit key)', false, 'stepping-stone-site-package');
    }

    public function render(): string
    {
        $key = $this->arguments['key'];

        if ($key !== null) {
            $value = LocalizationUtility::translate($key, $this->arguments['extensionName']) ?? (string)$this->arguments['value'];
        } else {
            $value = (string)$this->arguments['value'];
        }

        // Umlaute und ß transliterieren
        $value = str_replace(
            ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'],
            ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'],
            $value
        );

        // Verbleibende Sonderzeichen transliterieren (z. B. é, à, ...)
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim($value, '-');

        return $value;
    }
}
