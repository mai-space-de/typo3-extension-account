<?php

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'mai_account',
        'Login',
        'maispace: Login / Password-Reset',
        null,
        null,
        '',
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'mai_account',
        'Registration',
        'maispace: Registration',
        null,
        null,
        '',
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'mai_account',
        'Mfa',
        'maispace: MFA Setup & Verification',
        null,
        null,
        '',
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'mai_account',
        'Profile',
        'maispace: Profile Dashboard & Interests',
        null,
        null,
        '',
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
})();
