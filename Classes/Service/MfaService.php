<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

class MfaService
{
    public function generateSecret(): string
    {
        return Base32::encodeUpperUnpadded(random_bytes(20));
    }

    public function getQrCodeUri(string $secret, string $accountName, string $issuer = 'BGM Pulheim'): string
    {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($accountName);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri();
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $totp = TOTP::createFromSecret($secret);

        return $totp->verify($code);
    }
}
