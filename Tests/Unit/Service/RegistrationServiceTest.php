<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Service;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Service\RegistrationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class RegistrationServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private Random&MockObject $random;
    private RegistrationService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->random = $this->createMock(Random::class);

        $this->subject = new RegistrationService(
            $this->userRepository,
            $this->persistenceManager,
            $this->random,
        );
    }

    public function testConfirmEmailReturnNullForExpiredToken(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findByConfirmationToken')
            ->with('invalid-token')
            ->willReturn(null);

        $result = $this->subject->confirmEmail('invalid-token');

        self::assertNull($result);
    }

    public function testConfirmEmailActivatesUser(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->expects(self::once())->method('setEmailConfirmed')->with(true);
        $user->expects(self::once())->method('setDisable')->with(false);
        $user->expects(self::once())->method('setConfirmationToken')->with('');
        $user->expects(self::once())->method('setConfirmationTokenExpires')->with(0);

        $this->userRepository
            ->method('findByConfirmationToken')
            ->willReturn($user);

        $this->userRepository->expects(self::once())->method('update')->with($user);
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $result = $this->subject->confirmEmail('valid-token');

        self::assertSame($user, $result);
    }

    public function testResetPasswordReturnsFalseForInvalidToken(): void
    {
        $this->userRepository
            ->method('findByPasswordResetToken')
            ->willReturn(null);

        $result = $this->subject->resetPassword('bad-token', 'newpassword');

        self::assertFalse($result);
    }

    public function testResetPasswordUpdatesUserPassword(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->expects(self::once())->method('setPassword');
        $user->expects(self::once())->method('setPasswordResetToken')->with('');
        $user->expects(self::once())->method('setPasswordResetTokenExpires')->with(0);

        $this->userRepository
            ->method('findByPasswordResetToken')
            ->willReturn($user);

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $result = $this->subject->resetPassword('good-token', 'newPassword1!');

        self::assertTrue($result);
    }

    public function testInitiatePasswordResetReturnsTrueEvenForUnknownEmail(): void
    {
        $this->userRepository
            ->method('findByEmail')
            ->willReturn(null);

        $result = $this->subject->initiatePasswordReset('unknown@example.com', 'https://example.com/reset');

        self::assertTrue($result);
    }
}
