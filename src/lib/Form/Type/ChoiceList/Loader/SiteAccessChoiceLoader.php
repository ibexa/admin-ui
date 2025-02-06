<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class SiteAccessChoiceLoader implements ChoiceLoaderInterface
{
    private SiteaccessResolverInterface $nonAdminSiteAccessResolver;

    private ?Location $location;

    private SiteAccessNameGeneratorInterface $siteAccessNameGenerator;

    private ?string $languageCode;

    public function __construct(
        SiteaccessResolverInterface $nonAdminSiteAccessResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        ?Location $location = null,
        ?string $languageCode = null
    ) {
        $this->nonAdminSiteAccessResolver = $nonAdminSiteAccessResolver;
        $this->location = $location;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
        $this->languageCode = $languageCode;
    }

    /**
     * @return array<string, string>
     */
    public function getChoiceList(): array
    {
        $siteAccesses = $this->location === null
            ? $this->nonAdminSiteAccessResolver->getSiteAccessesList()
            : $this->nonAdminSiteAccessResolver->getSiteAccessesListForLocation(
                $this->location,
                null,
                $this->languageCode,
            );

        $data = [];
        foreach ($siteAccesses as $siteAccess) {
            $siteAccessKey = $this->siteAccessNameGenerator->generate($siteAccess);
            $data[$siteAccessKey] = $siteAccess->name;
        }

        return $data;
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $choices = $this->getChoiceList();

        return new ArrayChoiceList($choices, $value);
    }

    /**
     * @return string[]
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

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
