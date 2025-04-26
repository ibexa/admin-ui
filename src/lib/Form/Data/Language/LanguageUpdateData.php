<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data\Language;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Component\Validator\Constraints as Assert;

class LanguageUpdateData
{
    private ?Language $language;

    #[Assert\NotBlank]
    private ?string $name = null;

    private bool $enabled = false;

    public function __construct(?Language $language = null)
    {
        $this->language = $language;
        if ($language !== null) {
            $this->name = $language->name;
            $this->enabled = $language->enabled;
        }
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
