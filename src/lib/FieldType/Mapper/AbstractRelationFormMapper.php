<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;

abstract class AbstractRelationFormMapper implements FieldDefinitionFormMapperInterface
{
    public function __construct(
        protected ContentTypeService $contentTypeService,
        protected LocationService $locationService
    ) {
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
                $contentTypeHash[$contentType->getName()] = $contentType->getIdentifier();
            }
        }
        ksort($contentTypeHash);

        return $contentTypeHash;
    }
}
