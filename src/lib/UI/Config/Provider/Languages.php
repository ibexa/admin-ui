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
class Languages implements ProviderInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var string[] */
    private $siteAccesses;

    /** @var \Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface */
    private $siteAccessService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param string[]
     */
    public function __construct(
        LanguageService $languageService,
        ConfigResolverInterface $configResolver,
        SiteAccessServiceInterface $siteAccessService,
        array $siteAccesses
    ) {
        $this->languageService = $languageService;
        $this->configResolver = $configResolver;
        $this->siteAccessService = $siteAccessService;
        $this->siteAccesses = $siteAccesses;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'mappings' => $languagesMap = $this->getLanguagesMap(),
            'priority' => $this->getLanguagesPriority($languagesMap),
        ];
    }

    /**
     * @return array
     */
    protected function getLanguagesMap(): array
    {
        $languagesMap = [];

        foreach ($this->languageService->loadLanguages() as $language) {
            $languagesMap[$language->languageCode] = [
                'name' => $language->name,
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'enabled' => $language->enabled,
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
     * @param array $languagesMap data from call to getLanguagesMap()
     *
     * @return array
     */
    protected function getLanguagesPriority(array $languagesMap): array
    {
        $priority = [];
        $fallback = [];
        $siteAccessName = $this->siteAccessService->getCurrent()->name;
        $siteAccesses = array_unique(array_merge([$siteAccessName], $this->siteAccesses));

        foreach ($siteAccesses as $siteAccess) {
            $siteAccessLanguages = $this->configResolver->getParameter('languages', null, $siteAccess);
            $priority[] = array_shift($siteAccessLanguages);
            $fallback = array_merge($fallback, $siteAccessLanguages);
        }

        // Append fallback languages at the end of priority language list
        $languageCodes = array_unique(array_merge($priority, $fallback));

        $languages = array_filter(array_values($languageCodes), static function ($languageCode) use ($languagesMap) {
            // Get only Languages defined and enabled in Admin
            return isset($languagesMap[$languageCode]) && $languagesMap[$languageCode]['enabled'];
        });

        // Languages that are not configured in any of SiteAccess but user is still able to create content
        $unused = array_diff(array_keys($languagesMap), $languages);

        return array_merge($languages, $unused);
    }
}

class_alias(Languages::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Languages');
