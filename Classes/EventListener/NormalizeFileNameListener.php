<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Resource\Event\SanitizeFileNameEvent;

/**
 * Normalizes file names on upload/add to lowercase ASCII with underscores,
 * e.g. "Über Café-Menü 2024.PDF" becomes "ueber_cafe_menue_2024.pdf".
 */
#[AsEventListener]
final class NormalizeFileNameListener
{
    public function __construct(
        private readonly CharsetConverter $charsetConverter,
    ) {}

    public function __invoke(SanitizeFileNameEvent $event): void
    {
        $info = pathinfo($event->getFileName());
        $name = $info['filename'] ?? '';
        $extension = isset($info['extension']) && $info['extension'] !== ''
            ? '.' . strtolower($info['extension'])
            : '';

        $name = $this->charsetConverter->specCharsToASCII('utf-8', $name);
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9]+/', '_', $name);
        $name = trim($name, '_');

        if ($name === '') {
            $name = 'file';
        }

        $event->setFileName($name . $extension);
    }
}
