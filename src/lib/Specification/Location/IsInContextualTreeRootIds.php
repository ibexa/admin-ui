<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class IsInContextualTreeRootIds extends AbstractSpecification
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param Location $item
     */
    public function isSatisfiedBy($item): bool
    {
        $contextualRootIds = $this->configResolver->getParameter(
            'content_tree_module.contextual_tree_root_location_ids'
        );

        return in_array($item->getId(), $contextualRootIds, true);
    }
}
