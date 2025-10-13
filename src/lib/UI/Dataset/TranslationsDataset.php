<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class TranslationsDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language[] */
    protected array $data;

    public function __construct(
        protected readonly LanguageService $languageService,
        protected readonly ValueFactory $valueFactory
    ) {
    }

    public function load(VersionInfo $versionInfo): self
    {
        $languages = [];
        foreach ($versionInfo->getLanguageCodes() as $languageCode) {
            $languages[] = $this->languageService->loadLanguage($languageCode);
        }

        $this->data = array_map(
            function (Language $language) use ($versionInfo): UIValue\Content\Language {
                return $this->valueFactory->createLanguage($language, $versionInfo);
            },
            $languages
        );

        return $this;
    }

    public function loadFromContentType(ContentType $contentType): self
    {
        $languages = [];
        foreach ($contentType->languageCodes as $languageCode) {
            $languages[] = $this->languageService->loadLanguage($languageCode);
        }

        $this->data = array_map(
            function (Language $language) use ($contentType): UIValue\Content\Language {
                return $this->valueFactory->createLanguageFromContentType($language, $contentType);
            },
            $languages
        );

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getTranslations(): array
    {
        return $this->data;
    }

    /**
     * @return list<string>
     */
    public function getLanguageCodes(): array
    {
        return array_column($this->data, 'languageCode');
    }
}
