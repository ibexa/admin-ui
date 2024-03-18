<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Content;

use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode;
use Ibexa\AdminUi\REST\Value\ContentTree\Node;
use Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo;
use Ibexa\AdminUi\REST\Value\ContentTree\Root;
use Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type TPermissionRestrictions from \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo
 */
class ContentTreeController extends RestController
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    private PermissionCheckerInterface $permissionChecker;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    /** @var \Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory */
    private $contentTreeNodeFactory;

    public function __construct(
        LocationService $locationService,
        PermissionCheckerInterface $permissionChecker,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        NodeFactory $contentTreeNodeFactory
    ) {
        $this->locationService = $locationService;
        $this->permissionChecker = $permissionChecker;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
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

    /**
     * @return \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function loadNodeExtendedInfoAction(Request $request): NodeExtendedInfo
    {
        /** @var \Ibexa\AdminUi\REST\Value\ContentTree\LoadNodeExtendedInfoRequest $loadSubtreeRequest */
        $loadSubtreeRequest = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $locationId = $loadSubtreeRequest->locationId;
        $location = $this->locationService->loadLocation($locationId);
        $locationPermissionRestrictions = $this->getLocationPermissionRestrictions($location);

        return new NodeExtendedInfo($locationPermissionRestrictions);
    }

    /**
     * @return TPermissionRestrictions
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getLocationPermissionRestrictions(Location $location): array
    {
        $lookupCreateLimitationsResult = $this->permissionChecker->getContentCreateLimitations($location);
        $lookupUpdateLimitationsResult = $this->permissionChecker->getContentUpdateLimitations($location);
        $lookupDeleteLimitationsResult = $this->permissionChecker->getContentDeleteLimitations($location);
        $lookupHideLimitationsResult = $this->permissionChecker->getContentHideLimitations($location);

        $createLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupCreateLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        $updateLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupUpdateLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        $deleteLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupDeleteLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        $hideLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupHideLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        return [
            'create' => [
                'hasAccess' => $lookupCreateLimitationsResult->hasAccess(),
                'restrictedContentTypeIds' => $createLimitationsValues[Limitation::CONTENTTYPE],
                'restrictedLanguageCodes' => $createLimitationsValues[Limitation::LANGUAGE],
            ],
            'edit' => [
                'hasAccess' => $lookupUpdateLimitationsResult->hasAccess(),
                'restrictedContentTypeIds' => $updateLimitationsValues[Limitation::CONTENTTYPE],
                'restrictedLanguageCodes' => $updateLimitationsValues[Limitation::LANGUAGE],
            ],
            'delete' => [
                'hasAccess' => $lookupDeleteLimitationsResult->hasAccess(),
                'restrictedContentTypeIds' => $deleteLimitationsValues[Limitation::CONTENTTYPE],
                'restrictedLanguageCodes' => $deleteLimitationsValues[Limitation::LANGUAGE],
            ],
            'hide' => [
                'hasAccess' => $lookupHideLimitationsResult->hasAccess(),
                'restrictedContentTypeIds' => $hideLimitationsValues[Limitation::CONTENTTYPE],
                'restrictedLanguageCodes' => $hideLimitationsValues[Limitation::LANGUAGE],
            ],
        ];
    }
}

class_alias(ContentTreeController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\Content\ContentTreeController');
