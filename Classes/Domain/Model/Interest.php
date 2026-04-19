<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Interest extends AbstractEntity
{
    protected string $title = '';

    protected string $identifier = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
