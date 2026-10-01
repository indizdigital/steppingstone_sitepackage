<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Utility;

final class SlugUtility
{
    public static function slugify(string $value): string
    {
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

        return trim($value, '-');
    }
}