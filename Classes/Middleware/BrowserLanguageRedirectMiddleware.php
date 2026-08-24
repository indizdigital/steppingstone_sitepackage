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
        private const COOKIE_NAME = 'langDetected';
        private const CONSENT_COOKIE_NAME = 'ndz-cookie-consent';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Map to site language base
        $map = [
            'de' => '/de/',
            'en' => '/en/',
        ];

        $uri = (string)$request->getUri()->getPath();

        // No language segment in the path (e.g. site root) - redirect to the known/detected language
        if (rtrim($uri, '/') === '') {
            error_log($uri." by uri ",3,"/var/www/typo3/default/htdocs/vendor/indiz-digital-gmbh/stepping-stone-site-package/error.log");
            
            $cookies = $request->getCookieParams();

            if (isset($cookies[self::COOKIE_NAME])) {
                $target = $cookies[self::COOKIE_NAME];
                error_log($uri." by cookie",3,"/var/www/typo3/default/htdocs/vendor/indiz-digital-gmbh/stepping-stone-site-package/error.log");
            
            } else {
                error_log($uri." by browser",3,"/var/www/typo3/default/htdocs/vendor/indiz-digital-gmbh/stepping-stone-site-package/error.log");
            
                $acceptLang = $request->getHeaderLine('Accept-Language');
                $preferred = substr($acceptLang, 0, 2) ?: 'en';
                $target = $map[$preferred] ?? '/en/';
            }
            

            $response = new RedirectResponse($target, 302);

            if ($this->hasConsent($request)) {
                $cookie = self::COOKIE_NAME . '=' . $target . '; Path=/; SameSite=Lax';
                $response = $response->withAddedHeader('Set-Cookie', $cookie);
            }

            return $response;
        }

        // A language segment is already present in the path - keep the cookie in sync, no redirect
        $langCode = ltrim(substr($uri, 0, 3), '/');

        if(isset($map[$langCode])){
            $target = $map[$langCode];

            error_log($uri." ".$langCode."  ",3,"/var/www/typo3/default/htdocs/vendor/indiz-digital-gmbh/stepping-stone-site-package/error.log");
            $response = $handler->handle($request);

            if ($this->hasConsent($request)) {
                $cookie = self::COOKIE_NAME . '=' . $target . '; Path=/; SameSite=Lax';
                $response = $response->withAddedHeader('Set-Cookie', $cookie);
            }

            return $response;
        }

        return $handler->handle($request);
    }

    private function hasConsent(ServerRequestInterface $request): bool
    {
        $raw = $request->getCookieParams()[self::CONSENT_COOKIE_NAME] ?? null;

        if ($raw === null) {
            return false;
        }

        $consent = json_decode($raw, true);

        return is_array($consent) && ($consent['necessary'] ?? false) === true;
    }

}