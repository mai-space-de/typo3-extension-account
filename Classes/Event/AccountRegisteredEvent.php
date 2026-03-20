<?php

declare(strict_types=1);

namespace Maispace\Account\Event;

use Maispace\Account\Domain\Model\FrontendUser;

/**
 * PSR-14 event fired when a new account is registered.
 */
final class AccountRegisteredEvent
{
    public function __construct(
        private readonly FrontendUser $user
    ) {}

    public function getUser(): FrontendUser
    {
        return $this->user;
    }
}
