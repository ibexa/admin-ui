<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component;

use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Ibexa\TwigComponents\Component\Registry as TwigComponentsRegistry;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Registry} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\TwigComponents\Component\Registry} instead
 */
class Registry
{
    /**
     * @var string[]
     */
    private const COMPONENTS_WHITELIST =
        [
            'action-configuration-tabs',
            'attribute-definition-block',
            'attribute-definition-options-block',
            'attribute-group-block',
            'calendar-widget-before',
            'catalog-block',
            'content-create-form-after',
            'content-create-form-before',
            'content-edit-form-after',
            'content-edit-form-before',
            'content-edit-sections',
            'content-form-create-header-actions',
            'content-form-edit-header-actions',
            'content-tree-after',
            'content-tree-before',
            'content-type-edit-sections',
            'content-type-tab-groups',
            'customer-group-block',
            'dashboard-all-tab-groups',
            'dashboard-blocks',
            'dashboard-my-tab-groups',
            'discount-block',
            'discount-condition-code-summary',
            'discount-condition-code-usage-summary',
            'discount-condition-summary',
            'form-content-add-translation-body',
            'global-search',
            'global-search-autocomplete-templates',
            'header-user-menu-middle',
            'image-edit-actions-after',
            'infobar-options-before',
            'layout-content-after',
            'link-manager-block',
            'location-view-content-alerts',
            'location-view-tab-groups',
            'location-view-tabs-after',
            'login-form-after',
            'login-form-before',
            'order-details-summary-grid',
            'order-details-summary-stats',
            'payment-method-tabs',
            'product-block',
            'product-create-form-after',
            'product-create-form-header-actions',
            'product-edit-form-after',
            'product-edit-form-header-actions',
            'product-type-block',
            'script-body',
            'script-head',
            'shipment-summary-grid',
            'shipping-method-block',
            'stylesheet-body',
            'stylesheet-head',
            'systeminfo-tab-groups',
            'user-menu',
            'user-profile-blocks',
        ];

    private const GROUP_PREFIX = 'admin-ui-';

    protected TwigComponentsRegistry $inner;

    public function __construct(TwigComponentsRegistry $inner)
    {
        $this->inner = $inner;
    }

    public function addComponent(string $group, string $serviceId, ComponentInterface $component): void
    {
        $this->triggerDeprecation();
        $group = $this->prefixGroupIfNeeded($group);

        $this->inner->addComponent($group, $serviceId, $component);
    }

    /**
     * @return \Ibexa\Contracts\TwigComponents\ComponentInterface[]
     */
    public function getComponents(string $group): array
    {
        $this->triggerDeprecation();
        $group = $this->prefixGroupIfNeeded($group);

        return $this->inner->getComponents($group);
    }

    /**
     * @param \Ibexa\Contracts\TwigComponents\ComponentInterface[] $components
     */
    public function setComponents(string $group, array $components)
    {
        $this->triggerDeprecation();
        $group = $this->prefixGroupIfNeeded($group);

        $this->inner->setComponents($group, $components);
    }

    private function triggerDeprecation(): void
    {
        trigger_deprecation(
            'ibexa/admin-ui',
            '4.6.19',
            sprintf(
                'The %s is deprecated, will be removed in 5.0. Use %s instead',
                self::class,
                TwigComponentsRegistry::class
            )
        );
    }

    private function prefixGroupIfNeeded(string $group): string
    {
        if (in_array($group, self::COMPONENTS_WHITELIST, true)) {
            return self::GROUP_PREFIX . $group;
        }

        return $group;
    }
}

class_alias(Registry::class, 'EzSystems\EzPlatformAdminUi\Component\Registry');
