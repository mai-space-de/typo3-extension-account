<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Reminder extends AbstractEntity
{
    protected int $feUser = 0;

    protected string $title = '';

    protected \DateTimeImmutable|null $remindAt = null;

    protected bool $sent = false;

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function setFeUser(int $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getRemindAt(): ?\DateTimeImmutable
    {
        return $this->remindAt;
    }

    public function setRemindAt(?\DateTimeImmutable $remindAt): void
    {
        $this->remindAt = $remindAt;
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
