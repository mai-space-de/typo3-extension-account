<?php

declare(strict_types=1);

return [
    \Maispace\Account\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'newsletterOptin' => [
                'fieldName' => 'tx_account_newsletter_optin',
            ],
            'reminderEnabled' => [
                'fieldName' => 'tx_account_reminder_enabled',
            ],
            'memberReference' => [
                'fieldName' => 'tx_account_member_reference',
            ],
            'mfaSecret' => [
                'fieldName' => 'tx_account_mfa_secret',
            ],
            'mfaBackupCodes' => [
                'fieldName' => 'tx_account_mfa_backup_codes',
            ],
            'mfaEnabled' => [
                'fieldName' => 'tx_account_mfa_enabled',
            ],
            'confirmationToken' => [
                'fieldName' => 'tx_account_confirmation_token',
            ],
            'confirmed' => [
                'fieldName' => 'tx_account_confirmed',
            ],
            'interests' => [
                'fieldName' => 'tx_account_interests',
            ],
        ],
    ],
    \Maispace\Account\Domain\Model\Interest::class => [
        'tableName' => 'tx_account_interest',
    ],
    \Maispace\Account\Domain\Model\Reminder::class => [
        'tableName' => 'tx_account_reminder',
        'properties' => [
            'feUser' => [
                'fieldName' => 'fe_user',
            ],
            'eventUid' => [
                'fieldName' => 'event_uid',
            ],
            'eventTitle' => [
                'fieldName' => 'event_title',
            ],
            'eventDate' => [
                'fieldName' => 'event_date',
            ],
        ],
    ],
];
