<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Language;

/**
 * @todo Add validation
 */
class LanguagesDeleteData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[] $languages
     */
    public function __construct(protected array $languages = [])
    {
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[] $languages
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }
}
