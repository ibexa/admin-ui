<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Translation;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Symfony\Component\Validator\Constraints as Assert;

class MainTranslationUpdateData
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Content|null
     */
    #[Assert\NotBlank]
    public $content;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    public $languageCode;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content|null $content
     * @param string|null $languageCode
     */
    public function __construct(
        ?Content $content = null,
        ?string $languageCode = null
    ) {
        $this->content = $content;
        $this->languageCode = $languageCode;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content|null
     */
    public function getContent(): ?Content
    {
        return $this->content;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content|null $contentInfo
     */
    public function setContent(?Content $contentInfo = null): void
    {
        $this->content = $contentInfo;
    }

    /**
     * @return string|null
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string|null $languageCode
     */
    public function setLanguageCode(?string $languageCode = null): void
    {
        $this->languageCode = $languageCode;
    }
}
