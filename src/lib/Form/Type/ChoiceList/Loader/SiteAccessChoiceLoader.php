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
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class SiteAccessChoiceLoader implements ChoiceLoaderInterface
{
    /** @var \Ibexa\AdminUi\Siteaccess\NonAdminSiteaccessResolver */
    private SiteaccessResolverInterface $nonAdminSiteaccessResolver;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    private $location;

    private SiteAccessNameGeneratorInterface $siteAccessNameGenerator;

    public function __construct(
        SiteaccessResolverInterface $nonAdminSiteaccessResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        ?Location $location = null
    ) {
        $this->nonAdminSiteaccessResolver = $nonAdminSiteaccessResolver;
        $this->location = $location;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
    }

    /**
     * @return array<string, string>
     */
    public function getChoiceList(): array
    {
        $siteAccesses = $this->location === null
            ? $this->nonAdminSiteaccessResolver->getSiteAccessesList()
            : $this->nonAdminSiteaccessResolver->getSiteAccessesListForLocation(($this->location));

        $data = [];
        foreach ($siteAccesses as $siteAccess) {
            $siteAccessKey = $this->siteAccessNameGenerator->generate($siteAccess);
            $data[$siteAccessKey] = $siteAccess->name;
        }

        return $data;
    }

    public function loadChoiceList($value = null)
    {
        $choices = $this->getChoiceList();

        return new ArrayChoiceList($choices, $value);
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadValuesForChoices(array $choices, $value = null)
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
