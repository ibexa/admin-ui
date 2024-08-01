<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\SiteAccess;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;
use Ibexa\Core\MVC\Symfony\SiteAccess;

class IsAdmin extends AbstractSpecification
{
    /** @var array */
    private $siteAccessGroups;

    /**
     * @param array $siteAccessGroups
     */
    public function __construct(array $siteAccessGroups)
    {
        $this->siteAccessGroups = $siteAccessGroups;
    }

    /**
     * @param $item
     *
     * @return bool
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function isSatisfiedBy($item): bool
    {
        if (!$item instanceof SiteAccess) {
            throw new InvalidArgumentException($item, sprintf('Must be an instance of %s', SiteAccess::class));
        }

        return in_array($item->name, $this->siteAccessGroups[IbexaAdminUiBundle::ADMIN_GROUP_NAME], true);
    }
}
