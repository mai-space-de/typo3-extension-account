<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Repository;

use Maispace\Account\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
            $query->logicalAnd(
                $query->equals('txAccountConfirmationToken', $token),
                $query->greaterThan('txAccountConfirmationTokenExpires', time())
            )
        );

        /** @var FrontendUser|null */
        return $query->execute()->getFirst();
    }

    public function findByPasswordResetToken(string $token): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->logicalAnd(
                $query->equals('txAccountPasswordResetToken', $token),
                $query->greaterThan('txAccountPasswordResetTokenExpires', time())
            )
        );

        /** @var FrontendUser|null */
        return $query->execute()->getFirst();
    }

    public function findByEmail(string $email): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('email', $email)
        );

        /** @var FrontendUser|null */
        return $query->execute()->getFirst();
    }

    /**
     * Find all users who opted in for reminders and whose account is active.
     *
     * @return FrontendUser[]
     */
    public function findReminderOptinUsers(): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('txAccountRemindersOptin', true),
                $query->equals('txAccountEmailConfirmed', true),
                $query->equals('disable', false)
            )
        );

        return $query->execute()->toArray();
    }

    /**
     * Find all users who opted in for newsletter.
     *
     * @return FrontendUser[]
     */
    public function findNewsletterOptinUsers(): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('txAccountNewsletterOptin', true),
                $query->equals('txAccountEmailConfirmed', true),
                $query->equals('disable', false)
            )
        );

        return $query->execute()->toArray();
    }
}
