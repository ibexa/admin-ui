<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Config\AdminUiForms;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * @internal
 */
final class ContentTypeFieldTypesResolver implements ContentTypeFieldTypesResolverInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @return array<string, array{
     *     'position': int,
     *     'meta'?: bool,
     * }>
     */
    public function getFieldTypes(): array
    {
        if (!$this->configResolver->hasParameter(AdminUiForms::CONTENT_TYPE_FIELD_TYPES_PARAM)) {
            return [];
        }

        return $this->configResolver->getParameter(AdminUiForms::CONTENT_TYPE_FIELD_TYPES_PARAM);
    }

    public function getMetaFieldTypes(): array
    {
        $fieldTypes = $this->getFieldTypes();
        $metaFieldTypes = array_filter(
            $fieldTypes,
            static fn (array $config): bool => true === $config['meta']
        );

        $positions = array_column($metaFieldTypes, 'position');

        if (!empty($positions)) {
            array_multisort($positions, SORT_REGULAR, $metaFieldTypes);
        }

        return $metaFieldTypes;
    }

    /**
     * @return array<string>
     */
    public function getMetaFieldTypeIdentifiers(): array
    {
        return array_keys($this->getMetaFieldTypes());
    }
}
