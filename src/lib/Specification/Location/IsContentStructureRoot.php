<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class IsContentStructureRoot extends AbstractSpecification
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     */
    public function isSatisfiedBy($item): bool
    {
        return $item->getId() === (int)$this->configResolver->getParameter('location_ids.content_structure');
    }
}
