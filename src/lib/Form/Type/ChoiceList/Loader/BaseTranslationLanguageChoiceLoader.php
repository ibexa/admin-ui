<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;

class BaseTranslationLanguageChoiceLoader extends BaseChoiceLoader
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /** @var string[] */
    protected $languageCodes;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param string[] $languageCodes
     */
    public function __construct(LanguageService $languageService, $languageCodes)
    {
        $this->languageService = $languageService;
        $this->languageCodes = $languageCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceList(): array
    {
        return array_filter(
            $this->languageService->loadLanguages(),
            function (Language $language) {
                return $language->enabled && in_array($language->languageCode, $this->languageCodes, true);
            }
        );
    }
}
