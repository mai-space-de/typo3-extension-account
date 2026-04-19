<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\CType;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Account',
    $lang('plugin.account.title'),
    'ext-maispace-mai_account',
    'maispace_feature',
);

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Mfa',
    $lang('plugin.mfa.title'),
    'ext-maispace-mai_account',
    'maispace_feature',
);

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Stories',
    $lang('plugin.stories.title'),
    'ext-maispace-mai_account',
    'maispace_feature',
);

(new CType('maispace_account_account', $lang('ctype.account'), 'ext-maispace-mai_account'))
    ->addDefaultHeaderPalette()
    ->addCustomFields('pi_flexform')
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

(new CType('maispace_account_mfa', $lang('ctype.mfa'), 'ext-maispace-mai_account'))
    ->addDefaultHeaderPalette()
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

(new CType('maispace_account_stories', $lang('ctype.stories'), 'ext-maispace-mai_account'))
    ->addDefaultHeaderPalette()
    ->addCustomFields('pi_flexform')
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_account/Configuration/FlexForms/AccountPlugin.xml',
    'maispace_account_account',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_account/Configuration/FlexForms/StoryPlugin.xml',
    'maispace_account_stories',
);
