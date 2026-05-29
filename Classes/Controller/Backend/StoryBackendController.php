<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller\Backend;

use Maispace\MaiAccount\Domain\Model\Story;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Domain\Repository\StoryRepository;
use Maispace\MaiAccount\Service\AccountMailer;
use Maispace\MaiBase\Controller\Backend\AbstractBackendController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

#[AsController]
class StoryBackendController extends AbstractBackendController
{
    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        private readonly StoryRepository $storyRepository,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly AccountMailer $accountMailer,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {
        parent::__construct($moduleTemplateFactory, $iconFactory);
    }

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->createModuleTemplate();
        $this->addShortcutButton(
            $moduleTemplate,
            'mai_stories',
            'Stories',
        );

        $this->assignMultiple($moduleTemplate, [
            'stories' => $this->storyRepository->findAll(),
        ]);

        return $this->renderModuleResponse($moduleTemplate, 'Index');
    }

    public function approveAction(Story $story): ResponseInterface
    {
        $story->setStatus(Story::STATUS_PUBLISHED);
        $story->setPublishedAt(new \DateTimeImmutable());
        $this->storyRepository->update($story);
        $this->persistenceManager->persistAll();

        $feUserUid = $story->getFeUser();
        if ($feUserUid > 0) {
            $feUser = $this->frontendUserRepository->findByUid($feUserUid);
            if ($feUser !== null && $feUser->getEmail() !== '') {
                $this->accountMailer->sendStoryPublished(
                    $feUser->getEmail(),
                    $feUser->getFirstName(),
                    $story->getTitle(),
                );
            }
        }

        $this->flashSuccess(
            'Story approved and published.',
            $story->getTitle(),
        );

        return $this->redirect('index');
    }

    public function rejectAction(Story $story): ResponseInterface
    {
        $story->setStatus(Story::STATUS_REJECTED);
        $this->storyRepository->update($story);
        $this->persistenceManager->persistAll();

        $this->flashInfo(
            'Story marked as rejected.',
            $story->getTitle(),
        );

        return $this->redirect('index');
    }
}
