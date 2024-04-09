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
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-import-type TPermissionRestrictions from \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo
 */
class ContentTreeController extends RestController
{
    private LocationService $locationService;

    private PermissionCheckerInterface $permissionChecker;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    private NodeFactory $contentTreeNodeFactory;

    private PermissionResolver $permissionResolver;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        LocationService $locationService,
        PermissionCheckerInterface $permissionChecker,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        NodeFactory $contentTreeNodeFactory,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver
    ) {
        $this->locationService = $locationService;
        $this->permissionChecker = $permissionChecker;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->contentTreeNodeFactory = $contentTreeNodeFactory;
        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
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
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadNodeExtendedInfoAction(Location $location): NodeExtendedInfo
    {
        $locationPermissionRestrictions = $this->getLocationPermissionRestrictions($location);

        return new NodeExtendedInfo($locationPermissionRestrictions);
    }

    /**
     * @return TPermissionRestrictions
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getLocationPermissionRestrictions(Location $location): array
    {
        $lookupCreateLimitationsResult = $this->permissionChecker->getContentCreateLimitations($location);
        $lookupUpdateLimitationsResult = $this->permissionChecker->getContentUpdateLimitations($location);

        $createLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupCreateLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        $updateLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupUpdateLimitationsResult,
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
                'restrictedLanguageCodes' => $updateLimitationsValues[Limitation::LANGUAGE],
            ],
            'delete' => [
                'hasAccess' => $this->canUserRemoveContent($location),
            ],
            'hide' => [
                'hasAccess' => $this->canUserHideContent($location),
            ],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function canUserRemoveContent(Location $location): bool
    {
        $content = $location->getContent();
        $contentType = $content->getContentType();
        $contentIsUser = (new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))
            ->isSatisfiedBy($contentType);

        $translations = $content->getVersionInfo()->getLanguageCodes();
        $target = (new Target\Version())->deleteTranslations($translations);

        if ($contentIsUser) {
            return $this->permissionResolver->canUser(
                'content',
                'remove',
                $content,
                [$target]
            );
        }

        if ($location->depth > 1) {
            return $this->permissionResolver->canUser(
                'content',
                'remove',
                $location->getContentInfo(),
                [$location, $target]
            );
        }

        return false;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function canUserHideContent(Location $location): bool
    {
        $content = $location->getContent();

        $translations = $content->getVersionInfo()->getLanguageCodes();
        $target = (new Target\Version())->deleteTranslations($translations);

        return $this->permissionResolver->canUser(
            'content',
            'hide',
            $content,
            [$target]
        );
    }
}

class_alias(ContentTreeController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\Content\ContentTreeController');
