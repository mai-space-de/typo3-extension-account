<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Account',
    'description' => 'Frontend user extension providing login, registration, MFA, and full profile management including interests and reminders. Extends the TYPO3 core `felogin` plugin with MFA, registration, and profile features. Newsletter opt-in is a UI entry point only — subscriber records are owned by `mai_newsletter`.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'mai_member' => '13.4.0-14.99.99',
        ],
    ],
];
