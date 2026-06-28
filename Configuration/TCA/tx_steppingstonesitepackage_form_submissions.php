<?php

return [
    'ctrl' => [
        'title' => 'Form Submissions – Stepping Stone Test',
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
        'title' => [
            'label' => 'Title',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'first_name' => [
            'label' => 'First name',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'last_name' => [
            'label' => 'Surname',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'address_line_1' => [
            'label' => 'Address line 1',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'address_line_2' => [
            'label' => 'Address line 2',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'postcode' => [
            'label' => 'Postcode',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'location' => [
            'label' => 'Location',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'country' => [
            'label' => 'Country',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'phone' => [
            'label' => 'Mobile or phone',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'email' => [
            'label' => 'Email',
            'config' => ['type' => 'input', 'readOnly' => true],
        ],
        'terms_accepted' => [
            'label' => 'Terms accepted',
            'config' => ['type' => 'check', 'readOnly' => true],
        ],
        'newsletter' => [
            'label' => 'Newsletter',
            'config' => ['type' => 'check', 'readOnly' => true],
        ],
        'message' => [
            'label' => 'Questions or comments',
            'config' => ['type' => 'text', 'rows' => 5, 'readOnly' => true],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                company, title, first_name, last_name,
                --div--;Address, address_line_1, address_line_2, postcode, location, country,
                --div--;Contact, phone, email,
                --div--;Message, message,
                --div--;Options, terms_accepted, newsletter
            ',
        ],
    ],
];
