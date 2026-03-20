<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tempColumns = [
    'tx_account_interests' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_interests',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_account_interest',
            'MM' => 'tx_account_fe_users_interest_mm',
            'maxitems' => 99,
            'appearance' => [
                'collapseAll' => true,
                'enabledControls' => ['delete' => true],
            ],
        ],
    ],
    'tx_account_newsletter_optin' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_newsletter_optin',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
        ],
    ],
    'tx_account_reminder_enabled' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_reminder_enabled',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
        ],
    ],
    'tx_account_member_reference' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_member_reference',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_account_mfa_enabled' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_mfa_enabled',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'readOnly' => true,
        ],
    ],
    'tx_account_confirmed' => [
        'exclude' => true,
        'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_confirmed',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tab_account,
    tx_account_confirmed,tx_account_member_reference,tx_account_newsletter_optin,tx_account_reminder_enabled,tx_account_mfa_enabled,tx_account_interests'
);
