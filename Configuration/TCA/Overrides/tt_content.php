<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Helper;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Account',
    $lang('plugin.account.title'),
    'mai-content',
    'maispace_plugins_interactive',
    '',
    'FILE:EXT:mai_account/Configuration/FlexForms/AccountPlugin.xml',
);

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Register',
    $lang('plugin.register.title'),
    'mai-content',
    'maispace_plugins_interactive',
    '',
    'FILE:EXT:mai_account/Configuration/FlexForms/AccountPlugin.xml',
);

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Mfa',
    $lang('plugin.mfa.title'),
    'mai-content',
    'maispace_plugins_interactive',
);

ExtensionUtility::registerPlugin(
    'MaiAccount',
    'Stories',
    $lang('plugin.stories.title'),
    'mai-content',
    'maispace_plugins_interactive',
    '',
    'FILE:EXT:mai_account/Configuration/FlexForms/StoryPlugin.xml',
);
