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

final class ContentTypeEditData
{
    public function __construct(
        private ?ContentType $contentType = null,
        private ?ContentTypeGroup $contentTypeGroup = null,
        private ?Language $language = null
    ) {
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(?ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }
}
