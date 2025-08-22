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
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language */
    private $language;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $name;

    /** @var bool */
    private $enabled;

    public function __construct(?Language $language = null)
    {
        $this->language = $language;
        $this->name = $language->name;
        $this->enabled = $language->enabled;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }
}

class_alias(LanguageUpdateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Language\LanguageUpdateData');
