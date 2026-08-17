<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\ErrorHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ErrorHandler implements PageErrorHandlerInterface
{
    // Fallback target used when no matching page is found for the requested slug.
    // Can be overridden via the "fixedPageUid" key in the error handler's site configuration.
    private const DEFAULT_FALLBACK_PAGE_UID = 1;

    // Must match BrowserLanguageRedirectMiddleware::COOKIE_NAME
    private const LANGUAGE_COOKIE_NAME = 'langDetected';

    private int $statusCode;
    private array $errorHandlerConfiguration;

    public function __construct(int $statusCode, array $configuration)
    {
        $this->statusCode = $statusCode;
        $this->errorHandlerConfiguration = $configuration;
    }

    public function handlePageError(
        ServerRequestInterface $request,
        string $message,
        array $reasons = [],
    ): ResponseInterface {
        $site = $request->getAttribute('site');

        if (!$site instanceof Site) {
            return new HtmlResponse('<h1>Not found, sorry</h1>', $this->statusCode);
        }
        
        $slug = rtrim($request->getUri()->getPath(), '/') ?: '/';
        $language = $this->resolveBrowserPreferredLanguage($request, $site);

        $pageUid = $this->findPageUidBySlug($slug, $language);

        if ($pageUid !== null) {
            $uri = $site->getRouter()->generateUri($pageUid, ['_language' => $language]);

            return new RedirectResponse($uri, 307);
        }

        $fallbackPageUid = (int)($this->errorHandlerConfiguration['fixedPageUid'] ?? self::DEFAULT_FALLBACK_PAGE_UID);
        $fallbackUri = $site->getRouter()->generateUri($fallbackPageUid, ['_language' => $site->getDefaultLanguage()]);

        return new RedirectResponse($fallbackUri, 307);
    }

    private function resolveBrowserPreferredLanguage(ServerRequestInterface $request, Site $site): SiteLanguage
    {
        $availableLanguages = $site->getLanguages();

        // Prefer a language already detected/chosen in a previous request over re-resolving from the header
        $cookieValue = $request->getCookieParams()[self::LANGUAGE_COOKIE_NAME] ?? null;
        if ($cookieValue !== null) {
            $cookieLanguageCode = strtolower(trim($cookieValue, '/'));
            foreach ($availableLanguages as $language) {
                if (strtolower($language->getLocale()->getLanguageCode()) === $cookieLanguageCode) {
                    return $language;
                }
            }
        }

        $acceptLanguage = $request->getHeaderLine('Accept-Language');

        if ($acceptLanguage === '') {
            return $site->getDefaultLanguage();
        }

        $preferredLocales = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            [$locale, $quality] = array_pad(explode(';q=', trim($part)), 2, '1');
            $preferredLocales[trim($locale)] = (float)$quality;
        }
        arsort($preferredLocales);

        foreach (array_keys($preferredLocales) as $locale) {
            $twoLetterIsoCode = strtolower(substr($locale, 0, 2));
            foreach ($availableLanguages as $language) {
                if (strtolower($language->getLocale()->getLanguageCode()) === $twoLetterIsoCode) {
                    return $language;
                }
            }
        }

        return $site->getDefaultLanguage();
    }

    private function findPageUidBySlug(string $slug, SiteLanguage $language): ?int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class))
            ->add(GeneralUtility::makeInstance(StartTimeRestriction::class))
            ->add(GeneralUtility::makeInstance(EndTimeRestriction::class));

        $pageUid = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language->getLanguageId()),
                ),
                $queryBuilder->expr()->notIn('doktype', [199, 254, 255]),
            )
            ->executeQuery()
            ->fetchOne();

        return $pageUid !== false ? (int)$pageUid : null;
    }
}
