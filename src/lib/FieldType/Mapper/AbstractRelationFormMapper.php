<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;

abstract class AbstractRelationFormMapper implements FieldDefinitionFormMapperInterface
{
    protected ContentTypeService $contentTypeService;

    protected LocationService $locationService;

    public function __construct(ContentTypeService $contentTypeService, LocationService $locationService)
    {
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
    }

    /**
     * Fill a hash with all content types and their ids.
     *
     * @return string[]
     */
    protected function getContentTypesHash(): array
    {
        $contentTypeHash = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                $contentTypeHash[$contentType->getName()] = $contentType->identifier;
            }
        }
        ksort($contentTypeHash);

        return $contentTypeHash;
    }
}
