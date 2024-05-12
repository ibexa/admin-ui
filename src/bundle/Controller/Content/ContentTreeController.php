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
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\UI\Module\ContentTree\NodeFactory;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
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

    private SiteaccessResolverInterface  $siteaccessResolver;

    public function __construct(
        LocationService $locationService,
        PermissionCheckerInterface $permissionChecker,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        NodeFactory $contentTreeNodeFactory,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver,
        SiteaccessResolverInterface $siteaccessResolver
    ) {
        $this->locationService = $locationService;
        $this->permissionChecker = $permissionChecker;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->contentTreeNodeFactory = $contentTreeNodeFactory;
        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
        $this->siteaccessResolver = $siteaccessResolver;
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
                $sortOrder,
                $loadSubtreeRequest->filter,
            );
        }

        return new Root($elements);
    }

    /**
     * @internal for internal use by this package
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadNodeExtendedInfoAction(Location $location): NodeExtendedInfo
    {
        $locationPermissionRestrictions = $this->getLocationPermissionRestrictions($location);

        $content = $location->getContent();
        $versionInfo = $content->getVersionInfo();
        $translations = $versionInfo->languageCodes;
        $previewableTranslations = array_filter(
            $translations,
            fn (string $languageCode): bool => $this->isPreviewable($location, $content, $languageCode)
        );

        return new NodeExtendedInfo($locationPermissionRestrictions, $previewableTranslations);
    }

    /**
     * @return TPermissionRestrictions
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getLocationPermissionRestrictions(Location $location): array
    {
        $lookupCreateLimitationsResult = $this->permissionChecker->getContentCreateLimitations($location);
        $lookupUpdateLimitationsResult = $this->permissionChecker->getContentUpdateLimitations($location);

        $createLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupCreateLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );

        $updateLimitationsValues = $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupUpdateLimitationsResult,
            [Limitation::LANGUAGE]
        );

        return [
            'create' => [
                'hasAccess' => $lookupCreateLimitationsResult->hasAccess(),
                'restrictedContentTypeIds' => $createLimitationsValues[Limitation::CONTENTTYPE],
                'restrictedLanguageCodes' => $createLimitationsValues[Limitation::LANGUAGE],
            ],
            'edit' => [
                'hasAccess' => $lookupUpdateLimitationsResult->hasAccess(),
                // skipped content type limitation values as in this case it can be inferred from "hasAccess" above
                'restrictedLanguageCodes' => $updateLimitationsValues[Limitation::LANGUAGE],
            ],
            'delete' => [
                'hasAccess' => $this->canUserRemoveContent($location),
                // skipped other limitation values due to performance, until really needed
            ],
            'hide' => [
                'hasAccess' => $this->canUserHideContent($location),
                // skipped other limitation values due to performance, until really needed
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

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function isPreviewable(
        Location $location,
        Content $content,
        string $languageCode
    ): bool {
        $versionNo = $content->getVersionInfo()->versionNo;

        $siteAccesses = $this->siteaccessResolver->getSiteAccessesListForLocation(
            $location,
            $versionNo,
            $languageCode
        );

        $canPreview = $this->permissionResolver->canUser(
            'content',
            'versionread',
            $content,
            [$location]
        );

        return $canPreview && !empty($siteAccesses);
    }
}

class_alias(ContentTreeController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\Content\ContentTreeController');
