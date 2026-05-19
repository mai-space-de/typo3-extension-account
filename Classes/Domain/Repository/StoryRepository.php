<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Repository;

use Maispace\MaiAccount\Domain\Model\Story;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class StoryRepository extends Repository
{
    protected $defaultOrderings = [
        'submittedAt' => QueryInterface::ORDER_DESCENDING,
    ];

    public function findPublished(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('status', Story::STATUS_PUBLISHED),
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
