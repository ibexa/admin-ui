<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Translation;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

class TranslationDeleteData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[]|false[] $languageCodes
     */
    public function __construct(
        protected ?ContentInfo $contentInfo = null,
        protected array $languageCodes = []
    ) {
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]|false[]
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[]|false[] $languageCodes
     */
    public function setLanguageCodes(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }
}
