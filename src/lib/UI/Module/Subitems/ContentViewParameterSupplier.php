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
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Rest\Output\Generator\Json as JsonOutputGenerator;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentTypeInfoList as ContentTypeInfoListValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeInfoList;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\User\UserSetting\UserSettingService;

/**
 * @internal
 */
class ContentViewParameterSupplier
{
    /** @var \Ibexa\Contracts\Rest\Output\Visitor */
    private $outputVisitor;

    /** @var \Ibexa\Rest\Output\Generator\Json */
    private $outputGenerator;

    /** @var \Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentTypeInfoList */
    private $contentTypeInfoListValueObjectVisitor;

    /** @var \Ibexa\AdminUi\UI\Module\Subitems\ValueObjectVisitor\SubitemsList */
    private $subitemsListValueObjectVisitor;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\AdminUi\UI\Config\Provider\ContentTypeMappings */
    private $contentTypeMappings;

    /** @var \Ibexa\User\UserSetting\UserSettingService */
    private $userSettingService;

    /**
     * @param \Ibexa\Contracts\Rest\Output\Visitor $outputVisitor
     * @param \Ibexa\Rest\Output\Generator\Json $outputGenerator
     * @param \Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentTypeInfoList $contentTypeInfoListValueObjectVisitor
     * @param \Ibexa\AdminUi\UI\Module\Subitems\ValueObjectVisitor\SubitemsList $subitemsListValueObjectVisitor
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\AdminUi\UI\Config\Provider\ContentTypeMappings $contentTypeMappings
     * @param \Ibexa\User\UserSetting\UserSettingService $userSettingService
     */
    public function __construct(
        Visitor $outputVisitor,
        JsonOutputGenerator $outputGenerator,
        ContentTypeInfoListValueObjectVisitor $contentTypeInfoListValueObjectVisitor,
        SubitemsListValueObjectVisitor $subitemsListValueObjectVisitor,
        LocationService $locationService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        PermissionResolver $permissionResolver,
        ContentTypeMappings $contentTypeMappings,
        UserSettingService $userSettingService
    ) {
        $this->outputVisitor = $outputVisitor;
        $this->outputGenerator = $outputGenerator;
        $this->contentTypeInfoListValueObjectVisitor = $contentTypeInfoListValueObjectVisitor;
        $this->subitemsListValueObjectVisitor = $subitemsListValueObjectVisitor;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->permissionResolver = $permissionResolver;
        $this->contentTypeMappings = $contentTypeMappings;
        $this->userSettingService = $userSettingService;
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
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function supply(ContentView $view)
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] $contentTypes */
        $contentTypes = [];
        $subitemsRows = [];
        $location = $view->getLocation();
        $childrenCount = $this->locationService->getLocationChildCount($location);

        $subitemsLimit = (int)$this->userSettingService->getUserSetting('subitems_limit')->value;

        $locationChildren = $this->locationService->loadLocationChildren($location, 0, $subitemsLimit);
        foreach ($locationChildren->locations as $locationChild) {
            $contentType = $locationChild->getContent()->getContentType();

            if (!isset($contentTypes[$contentType->identifier])) {
                $contentTypes[$contentType->identifier] = $contentType;
            }

            $subitemsRows[] = $this->createSubitemsRow($locationChild, $contentType);
        }

        $subitemsList = new SubitemsList($subitemsRows, $childrenCount);
        $contentTypeInfoList = new ContentTypeInfoList($contentTypes, '');

        $subitemsListJson = $this->visitSubitemsList($subitemsList);
        $contentTypeInfoListJson = $this->visitContentTypeInfoList($contentTypeInfoList);

        $view->addParameters([
            'subitems_module' => [
                'items' => $subitemsListJson,
                'content_type_info_list' => $contentTypeInfoListJson,
                'content_create_permissions_for_mfu' => $this->getContentCreatePermissionsForMFU($view->getLocation(), $view->getContent()),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\Rest\Server\Values\RestContent
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
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

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\Rest\Server\Values\RestLocation
     */
    private function createRestLocation(Location $location): RestLocation
    {
        return new RestLocation(
            $location,
            $this->locationService->getLocationChildCount($location)
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsRow
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function createSubitemsRow(
        Location $location,
        ContentType $contentType
    ): SubitemsRow {
        $restLocation = $this->createRestLocation($location);
        $restContent = $this->createRestContent($location, $contentType);

        return new SubitemsRow($restLocation, $restContent);
    }

    /**
     * @param \Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsList $subitemsList
     *
     * @return string
     */
    private function visitSubitemsList(SubitemsList $subitemsList): string
    {
        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($subitemsList);
        $this->subitemsListValueObjectVisitor->visit($this->outputVisitor, $this->outputGenerator, $subitemsList);

        return $this->outputGenerator->endDocument($subitemsList);
    }

    /**
     * @param \Ibexa\Rest\Server\Values\ContentTypeInfoList $contentTypeInfoList
     *
     * @return string
     */
    private function visitContentTypeInfoList(ContentTypeInfoList $contentTypeInfoList): string
    {
        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($contentTypeInfoList);
        $this->contentTypeInfoListValueObjectVisitor->visit($this->outputVisitor, $this->outputGenerator, $contentTypeInfoList);

        return $this->outputGenerator->endDocument($contentTypeInfoList);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return array
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
            $locationCreateStruct = $this->locationService->newLocationCreateStruct($location->id);
            foreach ($contentTypeIdentifiers as $contentTypeIdentifier) {
                // TODO: Change to `contentTypeService->loadContentTypeList($restrictedContentTypesIds)` after #2444 will be merged
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
                $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $content->versionInfo->initialLanguageCode);
                $contentCreateStruct->sectionId = $location->contentInfo->sectionId;
                $createPermissionsInMfu[$contentTypeIdentifier] = $this->permissionResolver->canUser('content', 'create', $contentCreateStruct, [$locationCreateStruct]);
            }
        }

        return $createPermissionsInMfu;
    }
}

class_alias(ContentViewParameterSupplier::class, 'EzSystems\EzPlatformAdminUi\UI\Module\Subitems\ContentViewParameterSupplier');
