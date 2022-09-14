<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\AdminUi\Siteaccess\SiteAccessKeyGeneratorInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;

class SiteAccessLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    /** @var \Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface */
    private $siteAccessService;

    /** @var \Ibexa\AdminUi\Siteaccess\SiteAccessKeyGeneratorInterface */
    private $siteAccessKeyGenerator;

    public function __construct(
        SiteAccessServiceInterface $siteAccessService,
        SiteAccessKeyGeneratorInterface $siteAccessKeyGenerator
    ) {
        $this->siteAccessService = $siteAccessService;
        $this->siteAccessKeyGenerator = $siteAccessKeyGenerator;
    }

    protected function getSelectionChoices()
    {
        $siteAccesses = [];
        foreach ($this->siteAccessService->getAll() as $sa) {
            $siteAccesses[$this->siteAccessKeyGenerator->generate($sa->name)] = $sa->name;
        }

        return $siteAccesses;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];
        foreach ($this->siteAccessService->getAll() as $sa) {
            if (in_array($this->siteAccessKeyGenerator->generate($sa->name), $limitation->limitationValues)) {
                $values[] = $sa->name;
            }
        }

        return $values;
    }
}

class_alias(SiteAccessLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\SiteAccessLimitationMapper');
