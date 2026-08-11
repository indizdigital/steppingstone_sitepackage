<?php

declare(strict_types=1);

use IndizDigitalGmbh\SteppingStoneSitePackage\Middleware\BrowserLanguageRedirectMiddleware;

return [
    'frontend' => [
        'stepping-stone-site-package/browser-language-redirect' => [
            'target' => BrowserLanguageRedirectMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];