<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Event;

/**
 * PSR-14 event dispatched when a frontend user updates their interests.
 *
 * Consumers (e.g. maispace/newsletter) can listen to this event to sync
 * interest-based mailing list subscriptions.
 */
final class InterestsUpdatedEvent
{
    public function __construct(
        private readonly int $feUserUid,
        private readonly string $feUserEmail,
        private readonly array $newInterests,
        private readonly array $previousInterests,
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

    /**
     * Current (updated) list of interest identifiers.
     *
     * @return string[]
     */
    public function getNewInterests(): array
    {
        return $this->newInterests;
    }

    /**
     * Interest identifiers that were set before the update.
     *
     * @return string[]
     */
    public function getPreviousInterests(): array
    {
        return $this->previousInterests;
    }

    /**
     * Interests that were added in this update.
     *
     * @return string[]
     */
    public function getAddedInterests(): array
    {
        return array_values(array_diff($this->newInterests, $this->previousInterests));
    }

    /**
     * Interests that were removed in this update.
     *
     * @return string[]
     */
    public function getRemovedInterests(): array
    {
        return array_values(array_diff($this->previousInterests, $this->newInterests));
    }
}
