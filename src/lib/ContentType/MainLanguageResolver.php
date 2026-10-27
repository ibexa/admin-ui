<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\ContentType;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\NotFoundException;

/**
 * Resolves the language in which content types are created and edited by default.
 *
 * It is the first language from the SiteAccess "languages" setting that exists in the repository,
 * so a configured language missing from the database (e.g. eng-GB) does not block content type editing.
 *
 * @internal
 */
final class MainLanguageResolver
{
    private ConfigResolverInterface $configResolver;

    private LanguageService $languageService;

    public function __construct(
        ConfigResolverInterface $configResolver,
        LanguageService $languageService
    ) {
        $this->configResolver = $configResolver;
        $this->languageService = $languageService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException if none of the configured languages exists
     */
    public function resolve(): Language
    {
        $languageCodes = $this->getLanguageCodes();

        $existingLanguages = [];
        foreach ($this->languageService->loadLanguageListByCode($languageCodes) as $language) {
            $existingLanguages[$language->languageCode] = $language;
        }

        foreach ($languageCodes as $languageCode) {
            if (isset($existingLanguages[$languageCode])) {
                return $existingLanguages[$languageCode];
            }
        }

        throw new NotFoundException('Language', implode(', ', $languageCodes));
    }

    /**
     * Language codes checked by {@see resolve()}, in order of priority.
     *
     * @return string[]
     */
    public function getLanguageCodes(): array
    {
        $languageCodes = $this->configResolver->getParameter('languages');
        if (empty($languageCodes)) {
            return [$this->languageService->getDefaultLanguageCode()];
        }

        return $languageCodes;
    }
}
