<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentType\Translation;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\Validator\Constraints as Assert;

final class TranslationAddData
{
    public function __construct(
        #[Assert\NotBlank]
        private ?ContentType $contentType = null,
        #[Assert\NotBlank]
        private ?ContentTypeGroup $contentTypeGroup = null,
        #[Assert\NotBlank]
        private ?Language $language = null,
        private ?Language $baseLanguage = null
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

    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): self
    {
        $this->contentTypeGroup = $contentTypeGroup;

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

    public function getBaseLanguage(): ?Language
    {
        return $this->baseLanguage;
    }

    public function setBaseLanguage(?Language $baseLanguage): self
    {
        $this->baseLanguage = $baseLanguage;

        return $this;
    }
}
