<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\FieldType\ImageAsset;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * Provide information about ImageAsset Field Type mappings.
 */
class Mapping implements ProviderInterface
{
    private ConfigResolverInterface $configResolver;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $mappings = $this->configResolver->getParameter('fieldtypes.ibexa_image_asset.mappings');

        return [
            'contentTypeIdentifier' => $mappings['content_type_identifier'],
            'contentFieldIdentifier' => $mappings['content_field_identifier'],
            'nameFieldIdentifier' => $mappings['name_field_identifier'],
            'parentLocationId' => $mappings['parent_location_id'],
        ];
    }
}
