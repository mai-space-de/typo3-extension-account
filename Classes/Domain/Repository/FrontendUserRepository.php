<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Repository;

use Maispace\Account\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<FrontendUser>
 */
class FrontendUserRepository extends Repository
{
    public function findByConfirmationToken(string $token): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching(
            $query->equals('confirmationToken', $token)
        );
        /** @var FrontendUser|null $result */
        $result = $query->execute()->getFirst();
        return $result;
    }

    public function findByUsername(string $username): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching(
            $query->equals('username', $username)
        );
        /** @var FrontendUser|null $result */
        $result = $query->execute()->getFirst();
        return $result;
    }

    public function findByEmail(string $email): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching(
            $query->equals('email', $email)
        );
        /** @var FrontendUser|null $result */
        $result = $query->execute()->getFirst();
        return $result;
    }

    public function findUsersWithRemindersEnabled(): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('reminderEnabled', true),
                $query->equals('confirmed', true)
            )
        );
        return $query->execute()->toArray();
    }
}
