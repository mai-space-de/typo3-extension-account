<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiMail\Service\MailService;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AccountMailer
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly ViewFactoryInterface $viewFactory,
    ) {}

    public function sendRegistrationConfirmation(string $email, string $firstName, string $confirmUrl): void
    {
        $view = $this->createView();
        $view->assignMultiple([
            'firstName' => $firstName,
            'confirmUrl' => $confirmUrl,
        ]);

        $subject = (string) (LocalizationUtility::translate('email.confirm.subject', 'mai_account')
            ?: 'Please confirm your account');

        $this->mailService->queue($email, $subject, $view->render('Confirm'));
    }

    public function sendReminderNotification(string $email, string $firstName, string $reminderTitle, \DateTimeImmutable $remindAt): void
    {
        $view = $this->createView();
        $view->assignMultiple([
            'firstName' => $firstName,
            'reminderTitle' => $reminderTitle,
            'remindAt' => $remindAt,
        ]);

        $subject = (string) (LocalizationUtility::translate('email.reminder.subject', 'mai_account')
            ?: 'Reminder: ' . $reminderTitle);

        $this->mailService->queue($email, $subject, $view->render('Reminder'));
    }

    public function sendStoryPublished(string $email, string $firstName, string $storyTitle): void
    {
        $view = $this->createView();
        $view->assignMultiple([
            'firstName' => $firstName,
            'storyTitle' => $storyTitle,
        ]);

        $subject = (string) (LocalizationUtility::translate('email.storyPublished.subject', 'mai_account')
            ?: 'Your story has been published');

        $this->mailService->queue($email, $subject, $view->render('StoryPublished'));
    }

    private function createView(): ViewInterface
    {
        return $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:mai_account/Resources/Private/Templates/Email/'],
            partialRootPaths: ['EXT:mai_account/Resources/Private/Partials/'],
            layoutRootPaths: ['EXT:mai_account/Resources/Private/Layouts/'],
            format: 'html',
        ));
    }
}
