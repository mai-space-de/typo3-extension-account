<?php

declare(strict_types=1);

namespace Maispace\Account\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Model\Reminder;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Domain\Repository\ReminderRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ReminderService
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager
    ) {}

    /**
     * Create a reminder for a user from an event registration.
     *
     * @param array{eventUid: string, eventTitle: string, eventDate: \DateTimeImmutable} $eventData
     */
    public function createReminder(FrontendUser $user, array $eventData, int $storagePid): Reminder
    {
        $reminder = GeneralUtility::makeInstance(Reminder::class);
        $reminder->setPid($storagePid);
        $reminder->setFeUser($user);
        $reminder->setEventUid($eventData['eventUid']);
        $reminder->setEventTitle($eventData['eventTitle']);
        $reminder->setEventDate($eventData['eventDate']);
        $reminder->setSent(false);

        $this->reminderRepository->add($reminder);
        $this->persistenceManager->persistAll();

        return $reminder;
    }

    /**
     * Send all pending reminders that are due.
     * Called by the Scheduler task.
     *
     * @return int Number of reminders sent
     */
    public function sendPendingReminders(string $senderAddress, string $senderName): int
    {
        $now = new \DateTimeImmutable();
        $pending = $this->reminderRepository->findPendingReminders($now);
        $sent = 0;

        foreach ($pending as $reminder) {
            /** @var Reminder $reminder */
            $user = $reminder->getFeUser();
            if ($user === null || !$user->isReminderEnabled()) {
                continue;
            }

            $this->sendReminderEmail($reminder, $user, $senderAddress, $senderName);
            $reminder->setSent(true);
            $this->reminderRepository->update($reminder);
            $sent++;
        }

        if ($sent > 0) {
            $this->persistenceManager->persistAll();
        }

        return $sent;
    }

    private function sendReminderEmail(Reminder $reminder, FrontendUser $user, string $senderAddress, string $senderName): void
    {
        $eventDate = $reminder->getEventDate();
        $dateString = $eventDate !== null ? $eventDate->format('d.m.Y H:i') : '';

        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom([$senderAddress => $senderName])
            ->setTo([$user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName()])
            ->setSubject('Erinnerung: ' . $reminder->getEventTitle())
            ->html('<p>Hallo ' . htmlspecialchars($user->getFirstName()) . ',</p>'
                . '<p>Dies ist eine Erinnerung für das Event: <strong>' . htmlspecialchars($reminder->getEventTitle()) . '</strong></p>'
                . ($dateString ? '<p>Datum: ' . htmlspecialchars($dateString) . '</p>' : ''))
            ->send();
    }
}
