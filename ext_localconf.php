<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiAccount\Controller\AccountController;
use Maispace\MaiAccount\Controller\MfaController;
use Maispace\MaiAccount\Controller\StoryController;
use Maispace\MaiAccount\Task\ReminderTask;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'MaiAccount',
    'Account',
    [
        AccountController::class => 'login,logout,register,confirm,profile,changePassword,interests,reminders,newsletterOptIn',
    ],
    [
        AccountController::class => 'login,logout,register,confirm,profile,changePassword,interests,reminders,newsletterOptIn',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MaiAccount',
    'Register',
    [
        AccountController::class => 'register,confirm,login',
    ],
    [
        AccountController::class => 'register,confirm,login',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MaiAccount',
    'Mfa',
    [
        MfaController::class => 'setup,verify,disable',
    ],
    [
        MfaController::class => 'setup,verify,disable',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MaiAccount',
    'Stories',
    [
        StoryController::class => 'list,submit,detail',
    ],
    [
        StoryController::class => 'submit',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ReminderTask::class] = [
    'extension' => 'mai_account',
    'title' => 'LLL:EXT:mai_account/Resources/Private/Language/Default/locallang_tca.xlf:task.reminder.title',
    'description' => 'LLL:EXT:mai_account/Resources/Private/Language/Default/locallang_tca.xlf:task.reminder.description',
];
