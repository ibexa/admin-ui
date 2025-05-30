<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Draft;

use Ibexa\AdminUi\Validator\Constraints as AdminUiAssert;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * @todo Add validation.
 */
class ContentCreateData
{
    protected ?ContentType $contentType;

    /**
     * @AdminUiAssert\LocationIsContainer()
     */
    protected ?Location $parentLocation;

    protected ?Language $language;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null $contentType
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $parentLocation
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     */
    public function __construct(
        ?ContentType $contentType = null,
        ?Location $parentLocation = null,
        ?Language $language = null
    ) {
        $this->contentType = $contentType;
        $this->parentLocation = $parentLocation;
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
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return self
     */
    public function setContentType(ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getParentLocation(): ?Location
    {
        return $this->parentLocation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $parentLocation
     *
     * @return self
     */
    public function setParentLocation(Location $parentLocation): self
    {
        $this->parentLocation = $parentLocation;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     *
     * @return self
     */
    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
