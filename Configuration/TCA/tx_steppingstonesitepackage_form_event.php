<?php

return [
    'ctrl' => [
        'title' => 'Event Submissions – Stepping Stone',
        'label' => 'email',
        'label_alt' => 'first_name,last_name',
        'label_alt_force' => true,
        'crdate' => 'crdate',
        'tstamp' => 'tstamp',
        'delete' => 'deleted',
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/content/content-form.svg',
    ],
    'columns' => [
        'company' => [
            'label' => 'Company',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'firstname' => [
            'label' => 'First name',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'surname' => [
            'label' => 'Surname',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'venue' => [
            'label' => 'Venue',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'numberofpeople' => [
            'label' => 'numberofpeople',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'mobileorphone' => [
            'label' => 'Phone',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'email' => [
            'label' => 'Email',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'comment' => [
            'label' => 'comment',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                company, firstname, surname,venue,numberofpeople,mobileorphone,email,comment
            ',
        ],
    ],
];
