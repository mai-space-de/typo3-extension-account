<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ReminderRepository extends Repository
{
    protected $defaultOrderings = [
        'remindAt' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findDueReminders(\DateTimeImmutable $now): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->logicalAnd(
                $query->lessThanOrEqual('remindAt', $now->getTimestamp()),
                $query->equals('sent', false),
            ),
        );

        return $query->execute();
    }

    public function findByFeUser(int $feUserUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->equals('feUser', $feUserUid),
        );

        return $query->execute();
    }
}
