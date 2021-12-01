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
    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]|null */
    protected $contentTypes;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]|null $contentTypes
     */
    public function __construct(array $contentTypes = [])
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * @return array|null
     */
    public function getContentTypes(): ?array
    {
        return $this->contentTypes;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]|null $contentTypes
     */
    public function setContentTypes(?array $contentTypes)
    {
        $this->contentTypes = $contentTypes;
    }
}

class_alias(ContentTypesDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentType\ContentTypesDeleteData');
