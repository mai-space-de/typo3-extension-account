<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Reminder extends AbstractEntity
{
    protected ?FrontendUser $feUser = null;

    protected string $eventUid = '';

    protected string $eventTitle = '';

    protected ?\DateTimeImmutable $eventDate = null;

    protected bool $sent = false;

    public function getFeUser(): ?FrontendUser
    {
        return $this->feUser;
    }

    public function setFeUser(?FrontendUser $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getEventUid(): string
    {
        return $this->eventUid;
    }

    public function setEventUid(string $eventUid): void
    {
        $this->eventUid = $eventUid;
    }

    public function getEventTitle(): string
    {
        return $this->eventTitle;
    }

    public function setEventTitle(string $eventTitle): void
    {
        $this->eventTitle = $eventTitle;
    }

    public function getEventDate(): ?\DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeImmutable $eventDate): void
    {
        $this->eventDate = $eventDate;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): void
    {
        $this->sent = $sent;
    }
}
