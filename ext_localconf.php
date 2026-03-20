<?php

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'mai_account',
        'Login',
        [
            \Maispace\MaiAccount\Controller\LoginController::class => 'index, login, logout, passwordReset, passwordResetConfirm',
        ],
        [
            \Maispace\MaiAccount\Controller\LoginController::class => 'login, logout, passwordReset, passwordResetConfirm',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'mai_account',
        'Registration',
        [
            \Maispace\MaiAccount\Controller\RegistrationController::class => 'index, register, confirm',
        ],
        [
            \Maispace\MaiAccount\Controller\RegistrationController::class => 'register, confirm',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'mai_account',
        'Mfa',
        [
            \Maispace\MaiAccount\Controller\MfaController::class => 'setup, verify, backupCodes, disable',
        ],
        [
            \Maispace\MaiAccount\Controller\MfaController::class => 'setup, verify, backupCodes, disable',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'mai_account',
        'Profile',
        [
            \Maispace\MaiAccount\Controller\ProfileController::class => 'dashboard, updateInterests, updateNewsletter, updateProfile',
        ],
        [
            \Maispace\MaiAccount\Controller\ProfileController::class => 'updateInterests, updateNewsletter, updateProfile',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    // Register Scheduler task for reminder emails
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Maispace\MaiAccount\Task\SendRemindersTask::class] = [
        'extension' => 'mai_account',
        'title' => 'maispace Account: Send Event Reminders',
        'description' => 'Sends reminder emails to users who opted in for event reminders.',
        'additionalFields' => '',
    ];
})();
