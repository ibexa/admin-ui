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

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private ?string $name = null;

    /** @var bool */
    private bool $enabled;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     */
    public function __construct(Language $language = null)
    {
        $this->language = $language;
        $this->name = $language->name;
        $this->enabled = $language->enabled;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     */
    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
