<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Database\ConnectionPool;

class RegistrationService
{
    private const CONFIRM_TOKEN_TTL_SECONDS = 86400;

    public function __construct(
        private readonly PasswordHashFactory $passwordHashFactory,
        private readonly ConnectionPool $connectionPool,
        private readonly Random $random,
    ) {}

    /**
     * @return array{uid: int, token: string, expiresAt: int}
     */
    public function register(
        string $username,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        int $storagePid,
    ): array {
        $hashInstance = $this->passwordHashFactory->getDefaultHashInstance('FE');
        $hashedPassword = $hashInstance->getHashedPassword($password);

        $token = $this->random->generateRandomHexString(32);
        $expiresAt = time() + self::CONFIRM_TOKEN_TTL_SECONDS;

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $queryBuilder->insert('fe_users')
            ->values([
                'pid' => $storagePid,
                'tstamp' => time(),
                'crdate' => time(),
                'username' => $username,
                'password' => $hashedPassword,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'disable' => 1,
                'deleted' => 0,
                'tx_maiaccount_confirm_token' => $token,
                'tx_maiaccount_confirm_expires' => $expiresAt,
            ])
            ->executeStatement();

        $uid = (int) $this->connectionPool
            ->getConnectionForTable('fe_users')
            ->lastInsertId();

        return ['uid' => $uid, 'token' => $token, 'expiresAt' => $expiresAt];
    }

    public function confirm(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $qb->getRestrictions()->removeAll();
        $row = $qb->select('uid', 'tx_maiaccount_confirm_expires')
            ->from('fe_users')
            ->where($qb->expr()->eq('tx_maiaccount_confirm_token', $qb->createNamedParameter($token)))
            ->executeQuery()
            ->fetchAssociative();

        if ($row === false) {
            return false;
        }

        if ((int) $row['tx_maiaccount_confirm_expires'] > 0 && (int) $row['tx_maiaccount_confirm_expires'] < time()) {
            return false;
        }

        $updateQb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $updateQb->update('fe_users')
            ->set('disable', 0)
            ->set('tx_maiaccount_confirm_token', '')
            ->set('tx_maiaccount_confirm_expires', 0)
            ->set('tstamp', time())
            ->where($updateQb->expr()->eq('uid', $updateQb->createNamedParameter((int) $row['uid'])))
            ->executeStatement();

        return true;
    }

    public function isUsernameAvailable(string $username): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $count = (int) $queryBuilder
            ->count('uid')
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)))
            ->executeQuery()
            ->fetchOne();

        return $count === 0;
    }

    public function isEmailAvailable(string $email): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $count = (int) $queryBuilder
            ->count('uid')
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email)))
            ->executeQuery()
            ->fetchOne();

        return $count === 0;
    }
}
