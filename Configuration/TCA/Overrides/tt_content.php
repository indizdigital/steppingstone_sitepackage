<?php

defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['columns']['header']['config'] = [
    'type' => 'text',
    'rows' => 3,
    'eval' => 'trim',
];
$GLOBALS['TCA']['tt_content']['columns']['header_style'] = [
    'label' => 'Header Style',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Large', 'large'],
            ['Middle', 'middle'],
            ['Small', 'small'],
            ['Default', 'default'],
        ],
        'default' => 'middle',
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['header_layout']['config']['default'] = 2;

// Configuration/TCA/Overrides/tt_content.php
//$GLOBALS['TCA']['tt_content']['columns']['image']['l10n_mode'] = 'exclude';

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'header_style' => [
        'exclude' => true,
        'label' => 'Header Style',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 'Large', 'value' => 'large'],
                ['label' => 'Middle', 'value' => 'middle'],
                ['label' => 'Small', 'value' => 'small'],
            ],
            'default' => 'middle',
        ],
    ],
]);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'header_style',
    '',
    'after:header_layout'
);