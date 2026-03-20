<?php

defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'account',
    'Login',
    [\Maispace\Account\Controller\LoginController::class => 'index,login,logout'],
    [\Maispace\Account\Controller\LoginController::class => 'index,login,logout']
);

ExtensionUtility::configurePlugin(
    'account',
    'Registration',
    [\Maispace\Account\Controller\RegistrationController::class => 'index,register,confirm,passwordReset,passwordResetForm'],
    [\Maispace\Account\Controller\RegistrationController::class => 'index,register,confirm,passwordReset,passwordResetForm']
);

ExtensionUtility::configurePlugin(
    'account',
    'Mfa',
    [\Maispace\Account\Controller\MfaController::class => 'index,setup,verify,disable'],
    [\Maispace\Account\Controller\MfaController::class => 'index,setup,verify,disable']
);

ExtensionUtility::configurePlugin(
    'account',
    'Profile',
    [\Maispace\Account\Controller\ProfileController::class => 'index,edit,update,interests,updateInterests'],
    [\Maispace\Account\Controller\ProfileController::class => 'index,edit,update,interests,updateInterests']
);
