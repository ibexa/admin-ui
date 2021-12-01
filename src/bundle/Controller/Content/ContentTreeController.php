<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Content;

use Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode;
use Ibexa\AdminUi\REST\Value\ContentTree\Node;
use Ibexa\AdminUi\REST\Value\ContentTree\Root;
use Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

class ContentTreeController extends RestController
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory */
    private $contentTreeNodeFactory;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory $contentTreeNodeFactory
     */
    public function __construct(
        LocationService $locationService,
        NodeFactory $contentTreeNodeFactory
    ) {
        $this->locationService = $locationService;
        $this->contentTreeNodeFactory = $contentTreeNodeFactory;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadChildrenAction(
        Request $request,
        int $parentLocationId,
        int $limit,
        int $offset
    ): Node {
        $location = $this->locationService->loadLocation($parentLocationId);
        $loadSubtreeRequestNode = new LoadSubtreeRequestNode($parentLocationId, $limit, $offset);

        $sortClause = $request->query->get('sortClause', null);
        $sortOrder = $request->query->getAlpha('sortOrder', Query::SORT_ASC);

        return $this->contentTreeNodeFactory->createNode(
            $location,
            $loadSubtreeRequestNode,
            true,
            0,
            $sortClause,
            $sortOrder
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\AdminUi\REST\Value\ContentTree\Root
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function loadSubtreeAction(Request $request): Root
    {
        /** @var \Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequest $loadSubtreeRequest */
        $loadSubtreeRequest = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $sortClause = $request->query->get('sortClause', null);
        $sortOrder = $request->query->getAlpha('sortOrder', Query::SORT_ASC);

        $locationIdList = array_column($loadSubtreeRequest->nodes, 'locationId');
        $locations = $this->locationService->loadLocationList($locationIdList);

        $elements = [];
        foreach ($loadSubtreeRequest->nodes as $childLoadSubtreeRequestNode) {
            // avoid errors caused by i.e. permissions change
            if (!array_key_exists($childLoadSubtreeRequestNode->locationId, $locations)) {
                continue;
            }

            $location = $locations[$childLoadSubtreeRequestNode->locationId];
            $elements[] = $this->contentTreeNodeFactory->createNode(
                $location,
                $childLoadSubtreeRequestNode,
                true,
                0,
                $sortClause,
                $sortOrder
            );
        }

        return new Root($elements);
    }
}

class_alias(ContentTreeController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\Content\ContentTreeController');
