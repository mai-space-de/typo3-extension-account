<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'maispace Account',
    'description' => 'FE-User extension with Login, Registration, MFA (TOTP), Profile management, interests, newsletter opt-in, reminders and member reference.',
    'category' => 'plugin',
    'author' => 'maispace',
    'author_email' => '',
    'author_company' => 'maispace',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.99.99',
            'extbase' => '12.4.0-12.99.99',
            'fluid' => '12.4.0-12.99.99',
            'frontend' => '12.4.0-12.99.99',
            'scheduler' => '12.4.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
