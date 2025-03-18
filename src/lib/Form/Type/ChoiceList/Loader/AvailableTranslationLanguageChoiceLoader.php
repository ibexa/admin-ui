<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;

class AvailableTranslationLanguageChoiceLoader extends BaseChoiceLoader
{
    protected LanguageService $languageService;

    /** @var string[] */
    protected array $languageCodes;

    /**
     * @param string[] $languageCodes
     */
    public function __construct(LanguageService $languageService, array $languageCodes)
    {
        $this->languageService = $languageService;
        $this->languageCodes = $languageCodes;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getChoiceList(): array
    {
        return array_filter(
            iterator_to_array($this->languageService->loadLanguages()),
            function (Language $language): bool {
                return $language->enabled && !in_array($language->languageCode, $this->languageCodes, true);
            }
        );
    }
}
