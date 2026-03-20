<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Event;

/**
 * PSR-14 event dispatched when a frontend user registers for an event.
 *
 * This event is expected to be dispatched by maispace/project (or maispace/calendar).
 * ReminderService listens to this event to enqueue reminder emails.
 *
 * Define this class here as a shared contract; maispace/project may dispatch its
 * own copy – both must match the FQCN so the event dispatcher routes correctly.
 */
final class EventRegisteredEvent
{
    public function __construct(
        private readonly int $feUserUid,
        private readonly string $feUserEmail,
        private readonly int $eventUid,
        private readonly string $eventTitle,
        private readonly int $eventDate,
        private readonly string $eventLocation = '',
    ) {
    }

    public function getFeUserUid(): int
    {
        return $this->feUserUid;
    }

    public function getFeUserEmail(): string
    {
        return $this->feUserEmail;
    }

    public function getEventUid(): int
    {
        return $this->eventUid;
    }

    public function getEventTitle(): string
    {
        return $this->eventTitle;
    }

    public function getEventDate(): int
    {
        return $this->eventDate;
    }

    public function getEventLocation(): string
    {
        return $this->eventLocation;
    }
}
