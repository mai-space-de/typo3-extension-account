<?php

defined('TYPO3') or die();

(static function (): void {
    $additionalColumns = [
        'tx_account_member_ref' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_member_ref',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 64,
                'eval' => 'trim',
            ],
        ],
        'tx_account_interests' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_interests',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 3,
                'eval' => 'trim',
            ],
        ],
        'tx_account_newsletter_optin' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_newsletter_optin',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'tx_account_newsletter_optin_date' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_newsletter_optin_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'tx_account_reminders_optin' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_reminders_optin',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'tx_account_email_confirmed' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_email_confirmed',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'tx_account_mfa_enabled' => [
            'label' => 'LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tx_account_mfa_enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $additionalColumns);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        '--div--;LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tab_account,'
            . 'tx_account_member_ref,'
            . 'tx_account_email_confirmed,'
            . 'tx_account_newsletter_optin,'
            . 'tx_account_newsletter_optin_date,'
            . 'tx_account_reminders_optin,'
            . 'tx_account_interests,'
            . '--div--;LLL:EXT:account/Resources/Private/Language/locallang_db.xlf:fe_users.tab_security,'
            . 'tx_account_mfa_enabled'
    );
})();
