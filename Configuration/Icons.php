<?php

declare(strict_types=1);

$allIcons = 'EXT:mai_base/Resources/Public/Icons/all-icons/';

return [
    'ext-maispace-mai_account' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_account/Resources/Public/Icons/Extension.svg',
    ],
    'mai-account' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-login-key--entry-key-lock-login-pass-unlock--Streamline-Core.svg',
    ],
    'mai-account-register' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-user-add--actions-add-close-geometric-human-person-plus-single-up-user--Streamline-Core.svg',
    ],
    'mai-account-mfa' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-lock-shield--combination-combo-lock-locked-padlock-secure-security-shield-keyhole--Streamline-Core.svg',
    ],
    'mai-account-stories' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-text-formatting-quotation-1--quote-quotation-format-formatting-open-close-marks-text--Streamline-Core.svg',
    ],
    'mai-account-story' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-text-formatting-quotation-1--quote-quotation-format-formatting-open-close-marks-text--Streamline-Core.svg',
    ],
    'mai-account-interest' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-favorite-heart--reward-social-rating-media-heart-it-like-favorite-love--Streamline-Core.svg',
    ],
    'mai-account-reminder' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-alert-alarm-bell-2--alert-bell-ring-notification-alarm--Streamline-Core.svg',
    ],
    'mai-account-module' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'mail-chat-bubble-square--messages-message-bubble-chat-square--Streamline-Core.svg',
    ],
];
