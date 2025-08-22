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

class TranslationAddData
{
    /**
     * @Assert\NotBlank()
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null
     */
    private $contentType;

    /**
     * @Assert\NotBlank()
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null
     */
    private $contentTypeGroup;

    /**
     * @Assert\NotBlank()
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    private $language;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    private $baseLanguage;

    public function __construct(
        ?ContentType $contentType = null,
        ?ContentTypeGroup $contentTypeGroup = null,
        ?Language $language = null,
        ?Language $baseLanguage = null
    ) {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
        $this->language = $language;
        $this->baseLanguage = $baseLanguage;
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getBaseLanguage(): ?Language
    {
        return $this->baseLanguage;
    }

    public function setBaseLanguage(Language $baseLanguage): self
    {
        $this->baseLanguage = $baseLanguage;

        return $this;
    }
}

class_alias(TranslationAddData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentType\Translation\TranslationAddData');
