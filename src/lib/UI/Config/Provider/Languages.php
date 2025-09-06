<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;

/**
 * Provides information about languages.
 */
final readonly class Languages implements ProviderInterface
{
    /**
     * @param string[] $siteAccesses
     */
    public function __construct(
        private LanguageService $languageService,
        private ConfigResolverInterface $configResolver,
        private SiteAccessServiceInterface $siteAccessService,
        private array $siteAccesses
    ) {
    }

    public function getConfig(): array
    {
        return [
            'mappings' => $languagesMap = $this->getLanguagesMap(),
            'priority' => $this->getLanguagesPriority($languagesMap),
        ];
    }

    /**
     * @return array<string, array{name: string, id: int, languageCode: string, enabled: bool}>
     */
    private function getLanguagesMap(): array
    {
        $languagesMap = [];

        foreach ($this->languageService->loadLanguages() as $language) {
            $languagesMap[$language->getLanguageCode()] = [
                'name' => $language->getName(),
                'id' => $language->getId(),
                'languageCode' => $language->getLanguageCode(),
                'enabled' => $language->isEnabled(),
            ];
        }

        return $languagesMap;
    }

    /**
     * Returns list of languages in a prioritized form.
     *
     * First: languages that are main of siteaccesses are displayed first.
     * Next: fallback languages of siteaccesses.
     * Last: languages defined but not used in siteaccesses.
     *
     * @param array<string, array{name: string, id: int, languageCode: string, enabled: bool}> $languagesMap data from call to getLanguagesMap()
     *
     * @return array<mixed>
     */
    private function getLanguagesPriority(array $languagesMap): array
    {
        $priority = [];
        $siteAccess = $this->siteAccessService->getCurrent();
        if ($siteAccess === null) {
            return $this->siteAccesses;
        }

        $siteAccessName = $siteAccess->name;
        $siteAccesses = array_unique(array_merge([$siteAccessName], $this->siteAccesses));

        foreach ($siteAccesses as $siteAccess) {
            $siteAccessLanguages = $this->configResolver->getParameter('languages', null, $siteAccess);
            $priority = array_merge($priority, $siteAccessLanguages);
        }

        $languageCodes = array_unique($priority);

        $languages = array_filter(array_values($languageCodes), static function ($languageCode) use ($languagesMap): bool {
            // Get only Languages defined and enabled in Admin
            return isset($languagesMap[$languageCode]) && $languagesMap[$languageCode]['enabled'];
        });

        // Languages that are not configured in any of SiteAccess but user is still able to create content
        $unused = array_diff(array_keys($languagesMap), $languages);

        return array_merge($languages, $unused);
    }
}
