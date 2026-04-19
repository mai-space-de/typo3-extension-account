<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiMail\Service\MailService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AccountMailer
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
    }

    public function sendRegistrationConfirmation(string $email, string $firstName, string $confirmUrl): void
    {
        $view = $this->createView('Confirm');
        $view->assignMultiple([
            'firstName' => $firstName,
            'confirmUrl' => $confirmUrl,
        ]);

        $subject = (string)(LocalizationUtility::translate('email.confirm.subject', 'mai_account')
            ?: 'Please confirm your account');

        $this->mailService->queue($email, $subject, $view->render());
    }

    public function sendReminderNotification(string $email, string $firstName, string $reminderTitle, \DateTimeImmutable $remindAt): void
    {
        $view = $this->createView('Reminder');
        $view->assignMultiple([
            'firstName' => $firstName,
            'reminderTitle' => $reminderTitle,
            'remindAt' => $remindAt,
        ]);

        $subject = (string)(LocalizationUtility::translate('email.reminder.subject', 'mai_account')
            ?: 'Reminder: ' . $reminderTitle);

        $this->mailService->queue($email, $subject, $view->render());
    }

    private function createView(string $templateName): StandaloneView
    {
        $view = new StandaloneView();
        $view->setTemplateRootPaths(['EXT:mai_account/Resources/Private/Templates/Email/']);
        $view->setPartialRootPaths(['EXT:mai_account/Resources/Private/Partials/']);
        $view->setLayoutRootPaths(['EXT:mai_account/Resources/Private/Layouts/']);
        $view->setTemplate($templateName);
        $view->setFormat('html');

        return $view;
    }
}
