<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class TranslationsDataset
{
    /** @var LanguageService */
    protected $languageService;

    /** @var ValueFactory */
    protected $valueFactory;

    /** @var Language[] */
    protected $data;

    /**
     * @param LanguageService $languageService
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        LanguageService $languageService,
        ValueFactory $valueFactory
    ) {
        $this->languageService = $languageService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param VersionInfo $versionInfo
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
     * @param ContentType $contentType
     *
     * @return TranslationsDataset
     *
     * @throws NotFoundException
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
     * @return Language[]
     */
    public function getTranslations(): array
    {
        return $this->data;
    }

    /**
     * @return Language[]
     */
    public function getLanguageCodes(): array
    {
        return array_column($this->data, 'languageCode');
    }
}

class_alias(TranslationsDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\TranslationsDataset');
