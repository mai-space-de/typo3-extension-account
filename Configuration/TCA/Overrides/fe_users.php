<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Field;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\CheckboxConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectMultipleSideBySideConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

(new Field('fe_users', 'tx_maiaccount_mfa_enabled', $lang('fe_users.tx_maiaccount_mfa_enabled')))
    ->setConfig((new CheckboxConfig())->setRenderType('checkboxToggle'))
    ->registerField();

(new Field('fe_users', 'tx_maiaccount_mfa_secret', $lang('fe_users.tx_maiaccount_mfa_secret')))
    ->setConfig((new InputConfig())->setSize(50)->setMax(255)->setReadOnly())
    ->registerField();

(new Field('fe_users', 'tx_maiaccount_interests', $lang('fe_users.tx_maiaccount_interests')))
    ->setConfig([
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_maiaccount_interest',
        'foreign_table_where' => 'ORDER BY tx_maiaccount_interest.sorting ASC',
        'MM' => 'tx_maiaccount_feuser_interest_mm',
        'size' => 5,
        'maxitems' => 99,
    ])
    ->registerField();

(new Field('fe_users', 'tx_maiaccount_newsletter_optin', $lang('fe_users.tx_maiaccount_newsletter_optin')))
    ->setConfig((new CheckboxConfig())->setRenderType('checkboxToggle'))
    ->registerField();

if (ExtensionManagementUtility::isLoaded('mai_member')) {
    (new Field('fe_users', 'tx_maiaccount_member_uid', $lang('fe_users.tx_maiaccount_member_uid')))
        ->setConfig([
            'type' => 'group',
            'allowed' => 'tx_maimember_member',
            'size' => 1,
            'maxitems' => 1,
            'minitems' => 0,
        ])
        ->registerField();
}

(new Field('fe_users', 'tx_maiaccount_confirm_token', $lang('fe_users.tx_maiaccount_confirm_token')))
    ->setConfig((new InputConfig())->setSize(50)->setMax(128)->setReadOnly())
    ->registerField();

$accountFields = '--div--;' . $lang('tab.account') . ',
    tx_maiaccount_mfa_enabled, tx_maiaccount_mfa_secret,
    tx_maiaccount_interests,
    tx_maiaccount_newsletter_optin,';

if (ExtensionManagementUtility::isLoaded('mai_member')) {
    $accountFields .= '
    tx_maiaccount_member_uid,';
}

$accountFields .= '
    tx_maiaccount_confirm_token';

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    $accountFields,
);
