<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Service\MfaService;
use OTPHP\TOTP;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class MfaServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private Random&MockObject $random;
    private MfaService $subject;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->random = $this->createMock(Random::class);

        $this->random->method('generateRandomHexString')->willReturnCallback(
            static fn(int $length) => str_pad(dechex(random_int(1, 0xFFFFFF)), $length, '0', STR_PAD_LEFT)
        );

        $this->subject = new MfaService(
            $this->userRepository,
            $this->persistenceManager,
            $this->random
        );
    }

    public function testEnableStoresMfaSecretAndReturnsBackupCodes(): void
    {
        $user = new FrontendUser();
        $user->setEmail('test@example.com');

        $totp = TOTP::generate();
        $totp->setLabel('test@example.com');
        $secret = $totp->getSecret();
        $validCode = $totp->now();

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $backupCodes = $this->subject->enable($user, $secret, $validCode);

        self::assertTrue($user->isMfaEnabled());
        self::assertSame($secret, $user->getMfaSecret());
        self::assertCount(8, $backupCodes);
        self::assertCount(8, $user->getMfaBackupCodes());
    }

    public function testEnableThrowsOnInvalidCode(): void
    {
        $user = new FrontendUser();
        $user->setEmail('test@example.com');
        $totp = TOTP::generate();

        $this->expectException(\InvalidArgumentException::class);
        $this->subject->enable($user, $totp->getSecret(), '000000');
    }

    public function testVerifyReturnsTrueForValidCode(): void
    {
        $user = new FrontendUser();
        $user->setEmail('test@example.com');
        $totp = TOTP::generate();
        $secret = $totp->getSecret();

        $user->setMfaSecret($secret);
        $user->setMfaEnabled(true);
        $user->setMfaBackupCodes([]);

        $result = $this->subject->verify($user, $totp->now());

        self::assertTrue($result);
    }

    public function testVerifyReturnsFalseWhenMfaDisabled(): void
    {
        $user = new FrontendUser();
        $user->setMfaEnabled(false);

        self::assertFalse($this->subject->verify($user, '123456'));
    }

    public function testDisableClearsMfaData(): void
    {
        $user = new FrontendUser();
        $user->setMfaEnabled(true);
        $user->setMfaSecret('somesecret');

        $this->userRepository->expects(self::once())->method('update');

        $this->subject->disable($user);

        self::assertFalse($user->isMfaEnabled());
        self::assertSame('', $user->getMfaSecret());
        self::assertEmpty($user->getMfaBackupCodes());
    }
}
