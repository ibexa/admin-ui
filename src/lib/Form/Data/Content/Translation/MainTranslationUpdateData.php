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
    public function __construct(
        #[Assert\NotBlank]
        public ?Content $content = null,
        #[Assert\NotBlank]
        public ?string $languageCode = null
    ) {
    }

    public function getContent(): ?Content
    {
        return $this->content;
    }

    public function setContent(?Content $contentInfo = null): void
    {
        $this->content = $contentInfo;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode = null): void
    {
        $this->languageCode = $languageCode;
    }
}
