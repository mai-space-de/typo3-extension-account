<?php

declare(strict_types=1);

namespace Maispace\Account\EventListener;

use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Service\ReminderService;

/**
 * Listens for EventRegisteredEvent from maispace/project.
 * Creates a reminder for the registering FE user if reminders are enabled.
 *
 * The event class is referenced as a string to avoid a hard dependency on maispace/project.
 * If the class does not exist the listener simply does nothing.
 */
class EventRegisteredListener
{
    public function __construct(
        private readonly ReminderService $reminderService,
        private readonly FrontendUserRepository $frontendUserRepository
    ) {}

    public function __invoke(object $event): void
    {
        // Duck-type the event: it must provide getFeUserUid(), getEventUid(), getEventTitle(), getEventDate()
        if (!method_exists($event, 'getFeUserUid')
            || !method_exists($event, 'getEventUid')
            || !method_exists($event, 'getEventTitle')
            || !method_exists($event, 'getEventDate')
        ) {
            return;
        }

        $feUserUid = (int)$event->getFeUserUid();
        $user = $this->frontendUserRepository->findByUid($feUserUid);

        if ($user === null || !$user->isReminderEnabled()) {
            return;
        }

        $this->reminderService->createReminder(
            $user,
            [
                'eventUid' => (string)$event->getEventUid(),
                'eventTitle' => (string)$event->getEventTitle(),
                'eventDate' => $event->getEventDate(),
            ],
            $user->getPid()
        );
    }
}
