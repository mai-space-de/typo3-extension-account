<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Event\AccountConfirmedEvent;
use Maispace\Account\Event\AccountRegisteredEvent;
use Maispace\Account\Service\RegistrationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class RegistrationServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private Random&MockObject $random;
    private RegistrationService $subject;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->random = $this->createMock(Random::class);

        $this->subject = new RegistrationService(
            $this->userRepository,
            $this->persistenceManager,
            $this->eventDispatcher,
            $this->random
        );
    }

    public function testConfirmWithValidTokenConfirmsUser(): void
    {
        $token = 'validtoken123';
        $user = new FrontendUser();
        $user->setConfirmationToken($token);
        $user->setConfirmed(false);
        $user->setDisable(true);

        $this->userRepository
            ->expects(self::once())
            ->method('findByConfirmationToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('update')
            ->with($user);

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(AccountConfirmedEvent::class));

        $result = $this->subject->confirm($token);

        self::assertSame($user, $result);
        self::assertTrue($user->isConfirmed());
        self::assertFalse($user->isDisabled());
        self::assertSame('', $user->getConfirmationToken());
    }

    public function testConfirmWithInvalidTokenReturnsNull(): void
    {
        $this->userRepository
            ->method('findByConfirmationToken')
            ->willReturn(null);

        $result = $this->subject->confirm('invalidtoken');

        self::assertNull($result);
    }

    public function testResetPasswordWithValidToken(): void
    {
        $token = 'resettoken123';
        $user = new FrontendUser();
        $user->setConfirmationToken($token);

        $this->userRepository
            ->expects(self::once())
            ->method('findByConfirmationToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('update');

        $result = $this->subject->resetPassword($token, 'newpassword');

        self::assertSame($user, $result);
        self::assertSame('', $user->getConfirmationToken());
        self::assertNotEmpty($user->getPassword());
    }

    public function testResetPasswordWithInvalidTokenReturnsNull(): void
    {
        $this->userRepository
            ->method('findByConfirmationToken')
            ->willReturn(null);

        $result = $this->subject->resetPassword('invalid', 'newpass');

        self::assertNull($result);
    }
}
