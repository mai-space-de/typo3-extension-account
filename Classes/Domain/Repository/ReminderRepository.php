<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Repository;

use Maispace\Account\Domain\Model\Reminder;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Reminder>
 */
class ReminderRepository extends Repository
{
    public function findPendingReminders(\DateTimeImmutable $before): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('sent', false),
                $query->lessThanOrEqual('eventDate', $before->getTimestamp())
            )
        );
        return $query->execute()->toArray();
    }
}
