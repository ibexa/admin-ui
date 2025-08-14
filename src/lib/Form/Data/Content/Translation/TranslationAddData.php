<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Translation;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraints as Assert;

class TranslationAddData
{
    #[Assert\NotBlank]
    protected ?Location $location;

    #[Assert\NotBlank]
    protected ?Language $language;

    protected ?Language $baseLanguage;

    public function __construct(
        ?Location $location = null,
        ?Language $language = null,
        ?Language $baseLanguage = null
    ) {
        $this->location = $location;
        $this->language = $language;
        $this->baseLanguage = $baseLanguage;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

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
