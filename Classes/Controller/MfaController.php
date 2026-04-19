<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Service\MfaService;
use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;

class MfaController extends AbstractActionController
{
    use FlashMessageTrait;

    public function __construct(
        private readonly MfaService $mfaService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly Context $context,
    ) {
    }

    public function setupAction(): ResponseInterface
    {
        $feUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        $user = $this->frontendUserRepository->findByUid($feUserUid);
        $secret = $this->mfaService->generateSecret();
        $qrCodeUri = $this->mfaService->getQrCodeUri($secret, (string)$user->getUsername());

        $this->view->assignMultiple([
            'user' => $user,
            'secret' => $secret,
            'qrCodeUri' => $qrCodeUri,
        ]);

        return $this->htmlResponse();
    }

    public function verifyAction(): ResponseInterface
    {
        $feUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        return $this->htmlResponse();
    }

    public function disableAction(): ResponseInterface
    {
        $feUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        return $this->htmlResponse();
    }
}
