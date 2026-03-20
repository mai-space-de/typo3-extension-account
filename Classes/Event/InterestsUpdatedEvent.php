<?php

declare(strict_types=1);

namespace Maispace\Account\Event;

use Maispace\Account\Domain\Model\FrontendUser;

/**
 * PSR-14 event fired when a user updates their interests.
 * Consumed by maispace/newsletter for opt-in synchronisation.
 */
final class InterestsUpdatedEvent
{
    public function __construct(
        private readonly FrontendUser $user,
        private readonly array $previousInterestUids,
        private readonly array $newInterestUids
    ) {}

    public function getUser(): FrontendUser
    {
        return $this->user;
    }

    public function getPreviousInterestUids(): array
    {
        return $this->previousInterestUids;
    }

    public function getNewInterestUids(): array
    {
        return $this->newInterestUids;
    }
}
