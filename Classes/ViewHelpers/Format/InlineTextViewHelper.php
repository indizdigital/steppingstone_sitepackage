<?php

declare(strict_types=1);
namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers\Format;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InlineTextViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'value',
            'string',
            'Text zum Formatieren',
            false,
            ''
        );
    }

    public function render(): string
    {
        $text = $this->arguments['value'] ?? '';

        if ($text === '') {
            $text = $this->renderChildren() ?? '';
        }

        $text = htmlspecialchars((string)$text);

        // Fett
        $text = preg_replace(
            '/\*(.*?)\*/',
            '<strong>$1</strong>',
            $text
        );

        // Kursiv
        $text = preg_replace(
            '/_(.*?)_/',
            '<em>$1</em>',
            $text
        );

        // Farbmarkierung: {red|Text}
        $text = preg_replace_callback(
            '/\{(.*?)\|(.*?)\}/',
            function ($matches) {

                $allowedColors = [
                    'orange'
                ];

                $color = strtolower(trim($matches[1]));
                $content = $matches[2];

                // Nur erlaubte Farben zulassen
                if (!in_array($color, $allowedColors, true)) {
                    return $content;
                }

                return sprintf(
                    '<span class="text-%s">%s</span>',
                    $color,
                    $content
                );
            },
            $text
        );

        // Links
        $text = preg_replace(
            '/\[(.*?)\]\((.*?)\)/',
            '<a href="$2" target="_blank" rel="noopener">$1</a>',
            $text
        );

        // Zeilenumbrüche
        $text = nl2br($text);



        return $text;
    }

    public function isOutputEscapingEnabled(): bool
    {
        return false;
    }
}