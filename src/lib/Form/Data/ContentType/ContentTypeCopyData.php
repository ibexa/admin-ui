<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentType;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeCopyData
{
    /** @var ContentType */
    private $contentType;

    /** @var ContentTypeGroup */
    private $contentTypeGroup;

    /**
     * @param ContentType $contentType
     * @param ContentTypeGroup $contentTypeGroup
     */
    public function __construct(
        ?ContentType $contentType,
        ?ContentTypeGroup $contentTypeGroup
    ) {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
    }

    /**
     * @return ContentType|null
     */
    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    /**
     * @param ContentType|null $contentType
     */
    public function setContentType(?ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return ContentTypeGroup|null
     */
    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    /**
     * @param ContentTypeGroup|null $contentTypeGroup
     */
    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }
}

class_alias(ContentTypeCopyData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentType\ContentTypeCopyData');
