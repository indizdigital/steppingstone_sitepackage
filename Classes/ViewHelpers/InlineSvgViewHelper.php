<?php

namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers;

use DOMDocument;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InlineSvgViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'path',
            'mixed',
            'EXT:-Pfad oder TYPO3 FileReference',
            true
        );

        $this->registerArgument(
            'class',
            'string',
            'CSS-/Tailwind-Klassen',
            false,
            ''
        );

        $this->registerArgument(
            'title',
            'string',
            'Zugänglicher Titel für Screenreader',
            false,
            ''
        );

        $this->registerArgument(
            'decorative',
            'bool',
            'SVG als dekorativ markieren',
            false,
            false
        );
    }

    public function render(): string
    {
        $file = $this->arguments['path'];

        // Dateipfad ermitteln
        if ($file instanceof AbstractFile) {
            $path = $file->getForLocalProcessing(false);
        } elseif (is_string($file)) {
            $path = GeneralUtility::getFileAbsFileName($file);
        } else {
            return '';
        }

        if (!file_exists($path)) {
            return '';
        }

        // Nur SVG erlauben
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'svg') {
            return '';
        }

        $svgContent = file_get_contents($path);

        if (!$svgContent) {
            return '';
        }

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);

        if (!$dom->loadXML($svgContent)) {
            libxml_clear_errors();
            return '';
        }

        libxml_clear_errors();

        $svg = $dom->documentElement;

        if (!$svg || $svg->tagName !== 'svg') {
            return '';
        }

        // Klassen ergänzen
        $classes = trim(
            $svg->getAttribute('class')
            . ' '
            . $this->arguments['class']
        );

        if ($classes !== '') {
            $svg->setAttribute('class', $classes);
        }

        // WCAG: dekorativ
        if ($this->arguments['decorative']) {
            $svg->setAttribute('aria-hidden', 'true');
            $svg->setAttribute('focusable', 'false');
        }

        // WCAG: Titel für Screenreader
        elseif (!empty($this->arguments['title'])) {
            $titleId = uniqid('svg-title-');

            $title = $dom->createElement(
                'title',
                htmlspecialchars(
                    $this->arguments['title'],
                    ENT_QUOTES | ENT_XML1
                )
            );

            $title->setAttribute(
                'id',
                $titleId
            );

            $svg->insertBefore(
                $title,
                $svg->firstChild
            );

            $svg->setAttribute(
                'role',
                'img'
            );

            $svg->setAttribute(
                'aria-labelledby',
                $titleId
            );
        }

        return $dom->saveXML($svg);
    }
}