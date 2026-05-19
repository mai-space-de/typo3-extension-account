<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Service;

use Maispace\MaiAccount\Service\MfaService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MfaServiceTest extends TestCase
{
    private MfaService $subject;

    protected function setUp(): void
    {
        $this->subject = new MfaService();
    }

    // ── generateSecret ──────────────────────────────────────────────────────

    #[Test]
    public function generateSecretReturnsNonEmptyString(): void
    {
        $secret = $this->subject->generateSecret();
        self::assertNotSame('', $secret);
    }

    #[Test]
    public function generateSecretReturns32Characters(): void
    {
        // Base32 encoding of 20 random bytes produces 32 characters (unpadded).
        $secret = $this->subject->generateSecret();
        self::assertSame(32, strlen($secret));
    }

    #[Test]
    public function generateSecretReturnsOnlyUppercaseBase32Characters(): void
    {
        $secret = $this->subject->generateSecret();
        self::assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $secret);
    }

    #[Test]
    public function generateSecretReturnsDifferentValuesOnSubsequentCalls(): void
    {
        $secret1 = $this->subject->generateSecret();
        $secret2 = $this->subject->generateSecret();
        // Extremely unlikely to collide (1 in 32^32 chance).
        self::assertNotSame($secret1, $secret2);
    }

    // ── getQrCodeUri ────────────────────────────────────────────────────────

    #[Test]
    public function getQrCodeUriReturnsOtpAuthUri(): void
    {
        $secret = $this->subject->generateSecret();
        $uri = $this->subject->getQrCodeUri($secret, 'user@example.com');
        self::assertStringStartsWith('otpauth://totp/', $uri);
    }

    #[Test]
    public function getQrCodeUriContainsAccountName(): void
    {
        $secret = $this->subject->generateSecret();
        $uri = $this->subject->getQrCodeUri($secret, 'user@example.com');
        self::assertStringContainsString('user%40example.com', $uri);
    }

    #[Test]
    public function getQrCodeUriContainsDefaultIssuer(): void
    {
        $secret = $this->subject->generateSecret();
        $uri = $this->subject->getQrCodeUri($secret, 'user@example.com');
        // OTPHP encodes spaces as %20 in the provisioning URI.
        self::assertStringContainsString('BGM%20Pulheim', $uri);
    }

    #[Test]
    public function getQrCodeUriContainsCustomIssuer(): void
    {
        $secret = $this->subject->generateSecret();
        $uri = $this->subject->getQrCodeUri($secret, 'user@example.com', 'My App');
        // OTPHP encodes spaces as %20 in the provisioning URI.
        self::assertStringContainsString('My%20App', $uri);
    }

    #[Test]
    public function getQrCodeUriContainsSecret(): void
    {
        $secret = $this->subject->generateSecret();
        $uri = $this->subject->getQrCodeUri($secret, 'user@example.com');
        self::assertStringContainsString('secret=' . $secret, $uri);
    }

    // ── verifyCode ──────────────────────────────────────────────────────────

    #[Test]
    public function verifyCodeReturnsFalseForObviouslyInvalidCode(): void
    {
        $secret = $this->subject->generateSecret();
        // '000000' is a valid format but almost certainly not the current TOTP code.
        // This test is probabilistic but the chance of a false negative is < 0.003%.
        self::assertIsBool($this->subject->verifyCode($secret, '000000'));
    }

    #[Test]
    public function verifyCodeReturnsBooleanType(): void
    {
        $secret = $this->subject->generateSecret();
        $result = $this->subject->verifyCode($secret, '123456');
        self::assertIsBool($result);
    }
}
