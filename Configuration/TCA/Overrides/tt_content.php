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