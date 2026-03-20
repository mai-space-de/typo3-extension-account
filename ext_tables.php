<?php

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'account',
        'Login',
        'maispace: Login / Password-Reset'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'account',
        'Registration',
        'maispace: Registration'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'account',
        'Mfa',
        'maispace: MFA Setup & Verification'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'account',
        'Profile',
        'maispace: Profile Dashboard & Interests'
    );
})();
