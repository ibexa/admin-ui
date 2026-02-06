<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems;

use Ibexa\AdminUi\UI\Config\Provider\ContentTypeMappings;
use Ibexa\AdminUi\UI\Module\Subitems\ValueObjectVisitor\SubitemsList as SubitemsListValueObjectVisitor;
use Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsList;
use Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsRow;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\Query\QueryFactoryInterface;
use Ibexa\Rest\Output\Generator\Json as JsonOutputGenerator;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentTypeInfoList as ContentTypeInfoListValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeInfoList;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\User\UserSetting\UserSettingService;

/**
 * @internal
 */
final readonly class ContentViewParameterSupplier
{
    public function __construct(
        private Visitor $outputVisitor,
        private JsonOutputGenerator $outputGenerator,
        private ContentTypeInfoListValueObjectVisitor $contentTypeInfoListValueObjectVisitor,
        private SubitemsListValueObjectVisitor $subitemsListValueObjectVisitor,
        private LocationService $locationService,
        private ContentService $contentService,
        private ContentTypeService $contentTypeService,
        private PermissionResolver $permissionResolver,
        private ContentTypeMappings $contentTypeMappings,
        private UserSettingService $userSettingService,
        private QueryFactoryInterface $queryFactory,
        private SearchService $searchService
    ) {
    }

    /**
     * Fetches data for Subitems module to populate it with preloaded data.
     *
     * Why are we using REST stuff here?
     *
     * This is not so elegant but to preload data in Subitems module
     * we are using the same data structure it would use while
     * fetching data from the REST.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function supply(ContentView $view): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] $contentTypes */
        $contentTypes = [];
        $subitemsRows = [];
        $location = $view->getLocation();
        $subitemsLimit = (int)$this->userSettingService->getUserSetting('subitems_limit')->getValue();

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery $locationChildrenQuery */
        $locationChildrenQuery = $this->queryFactory->create('Children', ['location' => $location]);
        $locationChildrenQuery->offset = 0;
        $locationChildrenQuery->limit = $subitemsLimit;

        $searchResult = $this->searchService->findLocations($locationChildrenQuery);
        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $locationChild */
            $locationChild = $searchHit->valueObject;
            $contentType = $locationChild->getContent()->getContentType();

            if (!isset($contentTypes[$contentType->getIdentifier()])) {
                $contentTypes[$contentType->getIdentifier()] = $contentType;
            }

            $subitemsRows[] = $this->createSubitemsRow($locationChild, $contentType);
        }

        $subitemsList = new SubitemsList($subitemsRows, $searchResult->totalCount ?? 0);
        $contentTypeInfoList = new ContentTypeInfoList($contentTypes, '');

        $subitemsListJson = $this->visitSubitemsList($subitemsList);
        $contentTypeInfoListJson = $this->visitContentTypeInfoList($contentTypeInfoList);

        $view->addParameters([
            'subitems_module' => [
                'items' => $subitemsListJson,
                'content_type_info_list' => $contentTypeInfoListJson,
                'content_create_permissions_for_mfu' => $location === null
                    ? []
                    : $this->getContentCreatePermissionsForMFU($location, $view->getContent()),
            ],
        ]);
    }

    private function createRestContent(
        Location $location,
        ContentType $contentType
    ): RestContent {
        return new RestContent(
            $location->getContentInfo(),
            $location,
            $location->getContent(),
            $contentType,
            []
        );
    }

    private function createRestLocation(Location $location): RestLocation
    {
        return new RestLocation(
            $location,
            $this->locationService->getLocationChildCount(
                $location,
                // For the sub items module we only ever use the count to determine if there are children (0 or 1+),
                // hence setting a limit of 1 is sufficient here.
                1
            )
        );
    }

    private function createSubitemsRow(
        Location $location,
        ContentType $contentType
    ): SubitemsRow {
        $restLocation = $this->createRestLocation($location);
        $restContent = $this->createRestContent($location, $contentType);

        return new SubitemsRow($restLocation, $restContent);
    }

    private function visitSubitemsList(SubitemsList $subitemsList): string
    {
        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($subitemsList);
        $this->subitemsListValueObjectVisitor->visit(
            $this->outputVisitor,
            $this->outputGenerator,
            $subitemsList
        );

        return $this->outputGenerator->endDocument($subitemsList);
    }

    private function visitContentTypeInfoList(ContentTypeInfoList $contentTypeInfoList): string
    {
        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($contentTypeInfoList);
        $this->contentTypeInfoListValueObjectVisitor->visit(
            $this->outputVisitor,
            $this->outputGenerator,
            $contentTypeInfoList
        );

        return $this->outputGenerator->endDocument($contentTypeInfoList);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getContentCreatePermissionsForMFU(Location $location, Content $content): array
    {
        $createPermissionsInMfu = [];

        $hasAccess = $this->permissionResolver->hasAccess('content', 'create');
        $defaultContentTypeIdentifiers = array_column($this->contentTypeMappings->getConfig()['defaultMappings'], 'contentTypeIdentifier');
        $defaultContentTypeIdentifiers[] = $this->contentTypeMappings->getConfig()['fallbackContentType']['contentTypeIdentifier'];
        $contentTypeIdentifiers = array_unique($defaultContentTypeIdentifiers);

        if (\is_bool($hasAccess)) {
            foreach ($contentTypeIdentifiers as $contentTypeIdentifier) {
                $createPermissionsInMfu[$contentTypeIdentifier] = $hasAccess;
            }
        } else {
            $locationCreateStruct = $this->locationService->newLocationCreateStruct($location->getId());
            foreach ($contentTypeIdentifiers as $contentTypeIdentifier) {
                // TODO: Change to `contentTypeService->loadContentTypeList($restrictedContentTypesIds)` after #2444 will be merged
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
                $contentCreateStruct = $this->contentService->newContentCreateStruct(
                    $contentType,
                    $content->getVersionInfo()->getInitialLanguage()->getLanguageCode()
                );

                $contentCreateStruct->sectionId = $location->getContentInfo()->getSectionId();
                $createPermissionsInMfu[$contentTypeIdentifier] = $this->permissionResolver->canUser(
                    'content',
                    'create',
                    $contentCreateStruct,
                    [$locationCreateStruct]
                );
            }
        }

        return $createPermissionsInMfu;
    }
}
