<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Draft;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * @todo Add validation.
 */
class ContentCreateData
{
    public function __construct(
        protected ?ContentType $contentType = null,
        protected ?Location $parentLocation = null,
        protected ?Language $language = null
    ) {
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(?ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getParentLocation(): ?Location
    {
        return $this->parentLocation;
    }

    public function setParentLocation(?Location $parentLocation): self
    {
        $this->parentLocation = $parentLocation;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
