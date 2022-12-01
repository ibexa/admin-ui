<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

class ContentTree implements ProviderInterface
{
    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     */
    public function __construct(
        ConfigResolverInterface $configResolver
    ) {
        $this->configResolver = $configResolver;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $rootLocationId = $this->configResolver->getParameter('content_tree_module.tree_root_location_id');

        return [
            'loadMoreLimit' => $this->configResolver->getParameter('content_tree_module.load_more_limit'),
            'childrenLoadMaxLimit' => $this->configResolver->getParameter('content_tree_module.children_load_max_limit'),
            'treeMaxDepth' => $this->configResolver->getParameter('content_tree_module.tree_max_depth'),
            'allowedContentTypes' => $this->configResolver->getParameter('content_tree_module.allowed_content_types'),
            'ignoredContentTypes' => $this->configResolver->getParameter('content_tree_module.ignored_content_types'),
            'treeRootLocationId' => $rootLocationId ?? $this->configResolver->getParameter('content.tree_root.location_id'),
            'contextualTreeRootLocationIds' => $this->configResolver->getParameter('content_tree_module.contextual_tree_root_location_ids'),
        ];
    }
}

class_alias(ContentTree::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Module\ContentTree');
