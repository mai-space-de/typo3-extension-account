<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Domain\Model\Story;
use Maispace\MaiAccount\Domain\Repository\StoryRepository;
use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Maispace\MaiBase\Controller\Traits\PaginationTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;

class StoryController extends AbstractActionController
{
    use FlashMessageTrait;
    use PaginationTrait;

    public function __construct(
        private readonly StoryRepository $storyRepository,
        private readonly Context $context,
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $stories = $this->storyRepository->findPublished();
        ['paginator' => $paginator, 'pagination' => $pagination] = $this->paginateQueryResult($stories);

        $this->view->assignMultiple([
            'paginator' => $paginator,
            'pagination' => $pagination,
        ]);

        return $this->htmlResponse();
    }

    public function submitAction(): ResponseInterface
    {
        $feUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        return $this->htmlResponse();
    }

    public function detailAction(): ResponseInterface
    {
        $uid = (int)($this->request->hasArgument('uid') ? $this->request->getArgument('uid') : 0);
        $story = $uid > 0 ? $this->storyRepository->findByUid($uid) : null;

        $this->view->assign('story', $story);

        return $this->htmlResponse();
    }
}
