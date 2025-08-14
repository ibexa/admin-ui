<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentType\Translation;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\Validator\Constraints as Assert;

class TranslationRemoveData
{
    #[Assert\NotBlank]
    private ?ContentType $contentType;

    #[Assert\NotBlank]
    private ?ContentTypeGroup $contentTypeGroup;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    #[Assert\NotBlank]
    private array $languageCodes;

    /**
     * @param array $languageCodes
     */
    public function __construct(
        ?ContentType $contentType = null,
        ?ContentTypeGroup $contentTypeGroup = null,
        array $languageCodes = []
    ) {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
        $this->languageCodes = $languageCodes;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(ContentTypeGroup $contentTypeGroup): self
    {
        $this->contentTypeGroup = $contentTypeGroup;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[] $languageCodes
     */
    public function setLanguageCodes(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }
}
