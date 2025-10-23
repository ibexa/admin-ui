<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentTypeGroup;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

/**
 * @todo Add validation
 */
class ContentTypeGroupsDeleteData
{
    /**
     * @param ContentTypeGroup[]|null $contentTypeGroups
     */
    public function __construct(protected ?array $contentTypeGroups = []) {}

    /**
     * @return ContentTypeGroup[]|null
     */
    public function getContentTypeGroups(): ?array
    {
        return $this->contentTypeGroups;
    }

    /**
     * @param ContentTypeGroup[]|null $contentTypeGroups
     */
    public function setContentTypeGroups(?array $contentTypeGroups): void
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }
}
