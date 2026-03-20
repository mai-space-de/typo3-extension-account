<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Maispace Account',
    'description' => 'FE-User extension with Login, Registration, MFA, Profile management',
    'category' => 'plugin',
    'author' => 'Maispace',
    'author_email' => 'info@maispace.de',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.99.99',
            'extbase' => '12.4.0-12.99.99',
            'fluid' => '12.4.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
