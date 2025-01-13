<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentType;

/**
 * @todo Add validation
 */
class ContentTypesDeleteData
{
    /**
     * A map of content type id to false value.
     *
     * @var array<int, false>
     */
    protected array $contentTypes;

    /**
     * @param array<int, false> $contentTypes
     */
    public function __construct(array $contentTypes = [])
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * @return array<int, false>
     */
    public function getContentTypes(): array
    {
        return $this->contentTypes;
    }

    /**
     * @param array<int, false> $contentTypes
     */
    public function setContentTypes(array $contentTypes): void
    {
        $this->contentTypes = $contentTypes;
    }
}
