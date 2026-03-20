<?php

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder',
        'label' => 'event_title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'iconfile' => 'EXT:account/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'fe_user,event_uid,event_title,event_date,sent'],
    ],
    'columns' => [
        'fe_user' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder.fe_user',
            'config' => [
                'type' => 'group',
                'allowed' => 'fe_users',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'event_uid' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder.event_uid',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'event_title' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder.event_title',
            'config' => [
                'type' => 'input',
                'size' => 50,
            ],
        ],
        'event_date' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder.event_date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
        'sent' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:tx_account_reminder.sent',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
];
