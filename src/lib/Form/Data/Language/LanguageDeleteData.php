<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data\Language;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;

class LanguageDeleteData
{
    private ?Language $language;

    public function __construct(?Language $language = null)
    {
        $this->language = $language;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     */
    public function getLanguage(): ?Language
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
}
