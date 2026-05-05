<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Stepping Stone Site Package',
    'description' => '',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'fluid_styled_content' => '13.4.0-13.4.99',
            'rte_ckeditor' => '13.4.0-13.4.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'IndizDigitalGmbh\\SteppingStoneSitePackage\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Pascal Mürner',
    'author_email' => 'tech@indiz.digital',
    'author_company' => 'INDIZ Digital GmbH',
    'version' => '1.0.0',
];
