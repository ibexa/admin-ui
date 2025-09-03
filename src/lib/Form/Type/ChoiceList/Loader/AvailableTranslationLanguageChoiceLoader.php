<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;

final class AvailableTranslationLanguageChoiceLoader extends BaseChoiceLoader
{
    /**
     * @param string[] $languageCodes
     */
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly array $languageCodes
    ) {
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getChoiceList(): array
    {
        return array_filter(
            iterator_to_array($this->languageService->loadLanguages()),
            function (Language $language): bool {
                return $language->isEnabled()
                    && !in_array($language->getLanguageCode(), $this->languageCodes, true);
            }
        );
    }
}
