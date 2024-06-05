<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class TranslationsDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /** @var \Ibexa\AdminUi\UI\Value\ValueFactory */
    protected $valueFactory;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language[] */
    protected $data;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     */
    public function __construct(LanguageService $languageService, ValueFactory $valueFactory)
    {
        $this->languageService = $languageService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return TranslationsDataset
     */
    public function load(VersionInfo $versionInfo): self
    {
        $languages = [];
        foreach ($versionInfo->languageCodes as $languageCode) {
            $languages[] = $this->languageService->loadLanguage($languageCode);
        }

        $this->data = array_map(
            function (Language $language) use ($versionInfo) {
                return $this->valueFactory->createLanguage($language, $versionInfo);
            },
            $languages
        );

        return $this;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\AdminUi\UI\Dataset\TranslationsDataset
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadFromContentType(ContentType $contentType): self
    {
        $languages = [];
        foreach ($contentType->languageCodes as $languageCode) {
            $languages[] = $this->languageService->loadLanguage($languageCode);
        }

        $this->data = array_map(
            function (Language $language) use ($contentType) {
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getLanguageCodes(): array
    {
        return array_column($this->data, 'languageCode');
    }
}
