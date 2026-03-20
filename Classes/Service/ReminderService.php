<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Event\EventRegisteredEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ReminderService
 *
 * - Listens to EventRegisteredEvent (dispatched by maispace/project)
 * - Enqueues reminder entries for users who opted in for reminders
 * - SendRemindersTask (Scheduler) calls sendPendingReminders() periodically
 */
class ReminderService
{
    private const TABLE = 'tx_maiaccount_reminder_queue';

    /**
     * Default lead time: send reminder 24 hours before the event.
     */
    private const DEFAULT_LEAD_SECONDS = 86400;

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * PSR-14 event listener – invoked when a user registers for an event.
     */
    public function onEventRegistered(EventRegisteredEvent $event): void
    {
        $user = $this->frontendUserRepository->findByUid($event->getFeUserUid());

        if ($user === null || !$user->isRemindersOptin()) {
            return;
        }

        $remindAt = $event->getEventDate() - self::DEFAULT_LEAD_SECONDS;

        if ($remindAt <= time()) {
            // Event is too soon – skip
            return;
        }

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $connection->insert(self::TABLE, [
            'pid' => 0,
            'tstamp' => time(),
            'crdate' => time(),
            'fe_user_uid' => $event->getFeUserUid(),
            'event_uid' => $event->getEventUid(),
            'event_title' => $event->getEventTitle(),
            'event_date' => $event->getEventDate(),
            'event_location' => $event->getEventLocation(),
            'remind_at' => $remindAt,
            'sent' => 0,
        ]);
    }

    /**
     * Called by the Scheduler task to dispatch all due reminders.
     */
    public function sendPendingReminders(): int
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $queryBuilder = $connection->createQueryBuilder();

        $rows = $queryBuilder
            ->select('r.*', 'u.email', 'u.first_name', 'u.last_name')
            ->from(self::TABLE, 'r')
            ->join('r', 'fe_users', 'u', 'r.fe_user_uid = u.uid')
            ->where(
                $queryBuilder->expr()->eq('r.sent', 0),
                $queryBuilder->expr()->lte('r.remind_at', $queryBuilder->createNamedParameter(time(), Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('r.deleted', 0),
                $queryBuilder->expr()->eq('u.deleted', 0),
                $queryBuilder->expr()->eq('u.disable', 0),
                $queryBuilder->expr()->eq('u.tx_maiaccount_reminders_optin', 1),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $sent = 0;
        foreach ($rows as $row) {
            $this->sendReminderEmail($row);

            $connection->update(self::TABLE, ['sent' => 1], ['uid' => (int)$row['uid']]);
            $sent++;
        }

        return $sent;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function sendReminderEmail(array $row): void
    {
        $recipientName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: $row['email'];
        $eventDate = date('d.m.Y H:i', (int)$row['event_date']);

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->to($row['email'])
            ->subject('Erinnerung: ' . $row['event_title'])
            ->html(
                sprintf(
                    '<p>Hallo %s,</p>'
                    . '<p>dies ist eine Erinnerung an die bevorstehende Veranstaltung:</p>'
                    . '<p><strong>%s</strong><br>'
                    . 'Datum: %s<br>'
                    . 'Ort: %s</p>'
                    . '<p>Wir freuen uns auf Sie!</p>',
                    htmlspecialchars($recipientName),
                    htmlspecialchars($row['event_title']),
                    htmlspecialchars($eventDate),
                    htmlspecialchars($row['event_location'])
                )
            )
            ->send();
    }
}
