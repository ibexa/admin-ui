<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentType;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeEditData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null */
    private $contentType;

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null */
    private $contentTypeGroup;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null */
    private $language;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null $contentType
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null $contentTypeGroup
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     */
    public function __construct(
        ?ContentType $contentType = null,
        ?ContentTypeGroup $contentTypeGroup = null,
        ?Language $language = null
    ) {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
        $this->language = $language;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null
     */
    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null $contentType
     */
    public function setContentType(?ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null
     */
    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null $contentTypeGroup
     */
    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     */
    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }
}

class_alias(ContentTypeEditData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentType\ContentTypeEditData');
