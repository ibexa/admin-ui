<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final class Locations implements ProviderInterface
{
    private const MEDIA_IDENTIFIER = 'media';
    private const CONTENT_STRUCTURE_IDENTIFIER = 'contentStructure';
    private const USERS_IDENTIFIER = 'users';

    private ConfigResolverInterface $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver
    ) {
        $this->configResolver = $configResolver;
    }

    public function getConfig(): array
    {
        return [
            self::MEDIA_IDENTIFIER => $this->configResolver->getParameter('location_ids.media'),
            self::CONTENT_STRUCTURE_IDENTIFIER => $this->configResolver->getParameter('location_ids.content_structure'),
            self::USERS_IDENTIFIER => $this->configResolver->getParameter('location_ids.users'),
        ];
    }
}
