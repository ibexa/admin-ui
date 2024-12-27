<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ConfiguredLanguagesChoiceLoader implements ChoiceLoaderInterface
{
    private LanguageService $languageService;

    private ConfigResolverInterface $configResolver;

    public function __construct(LanguageService $languageService, ConfigResolverInterface $configResolver)
    {
        $this->languageService = $languageService;
        $this->configResolver = $configResolver;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function getChoiceList(): array
    {
        return $this->getPriorityOrderedLanguages();
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $choices = $this->getChoiceList();

        return new ArrayChoiceList($choices, $value);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @return string[]
     */
    public function loadValuesForChoices(array $choices, ?callable $value = null): array
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

    /**
     * Sort languages based on SiteAccess languages order.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    private function getPriorityOrderedLanguages(): array
    {
        $languages = $this->languageService->loadLanguages();
        $languagesAssoc = [];

        foreach ($languages as $language) {
            $languagesAssoc[$language->languageCode] = $language;
        }

        $orderedLanguages = [];
        $saLanguagesCodes = $this->configResolver->getParameter('languages');

        foreach ($saLanguagesCodes as $saLanguageCode) {
            if (isset($languagesAssoc[$saLanguageCode])) {
                $orderedLanguages[] = $languagesAssoc[$saLanguageCode];
                unset($languagesAssoc[$saLanguageCode]);
            }
        }

        return array_merge($orderedLanguages, array_values($languagesAssoc));
    }
}
