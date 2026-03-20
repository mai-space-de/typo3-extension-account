<?php

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'account',
        'Login',
        [
            \Maispace\Account\Controller\LoginController::class => 'index, login, logout, passwordReset, passwordResetConfirm',
        ],
        [
            \Maispace\Account\Controller\LoginController::class => 'login, logout, passwordReset, passwordResetConfirm',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'account',
        'Registration',
        [
            \Maispace\Account\Controller\RegistrationController::class => 'index, register, confirm',
        ],
        [
            \Maispace\Account\Controller\RegistrationController::class => 'register, confirm',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'account',
        'Mfa',
        [
            \Maispace\Account\Controller\MfaController::class => 'setup, verify, backupCodes, disable',
        ],
        [
            \Maispace\Account\Controller\MfaController::class => 'setup, verify, backupCodes, disable',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'account',
        'Profile',
        [
            \Maispace\Account\Controller\ProfileController::class => 'dashboard, updateInterests, updateNewsletter, updateProfile',
        ],
        [
            \Maispace\Account\Controller\ProfileController::class => 'updateInterests, updateNewsletter, updateProfile',
        ]
    );

    // Register Scheduler task for reminder emails
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Maispace\Account\Task\SendRemindersTask::class] = [
        'extension' => 'account',
        'title' => 'maispace Account: Send Event Reminders',
        'description' => 'Sends reminder emails to users who opted in for event reminders.',
        'additionalFields' => '',
    ];
})();
