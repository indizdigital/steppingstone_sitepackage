<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;

final class BrowserLanguageRedirectMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $site = $request->getAttribute('site');

        if ($site === null) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();

        // Prüfen, ob die URL bereits einen Sprachpräfix enthält
        foreach ($site->getLanguages() as $siteLanguage) {
            $base = trim((string)$siteLanguage->getBase(), '/');

            if ($base !== '' && (
                $path === '/' . $base ||
                str_starts_with($path, '/' . $base . '/')
            )) {
                // Sprache ist bereits Bestandteil der URL
                return $handler->handle($request);
            }
        }

        // Browser-Sprache ermitteln
        $language = $this->getBrowserLanguage(
            $request->getHeaderLine('Accept-Language')
        );

        if ($language === null) {
            return $handler->handle($request);
        }

        // Passende Site Language suchen
        foreach ($site->getLanguages() as $siteLanguage) {
            if (
                $siteLanguage->getLocale()->getLanguageCode() !== $language
            ) {
                continue;
            }

            $base = trim((string)$siteLanguage->getBase(), '/');

            if ($base === '') {
                return $handler->handle($request);
            }

            // Ursprünglichen Pfad hinter dem Sprachpräfix anhängen
            $targetPath = '/' . $base . $path;

            // Doppelte Slashes vermeiden
            $targetPath = preg_replace('#/+#', '/', $targetPath);

            // Query-String übernehmen
            $query = $request->getUri()->getQuery();

            if ($query !== '') {
                $targetPath .= '?' . $query;
            }

            return new RedirectResponse(
                $targetPath,
                302
            );
        }

        return $handler->handle($request);
    }

    private function getBrowserLanguage(string $acceptLanguage): ?string
    {
        if ($acceptLanguage === '') {
            return null;
        }

        $languages = [];

        foreach (explode(',', $acceptLanguage) as $language) {
            $parts = explode(';', trim($language));

            $code = strtolower(trim($parts[0]));

            if ($code === '' || $code === '*') {
                continue;
            }

            $quality = 1.0;

            if (
                isset($parts[1])
                && preg_match('/q=([0-9.]+)/', $parts[1], $matches)
            ) {
                $quality = (float)$matches[1];
            }

            if ($quality > 0) {
                $languages[$code] = $quality;
            }
        }

        arsort($languages);

        foreach ($languages as $language => $quality) {
            // de-CH → de
            return explode('-', $language)[0];
        }

        return null;
    }
}