<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final readonly class Locations implements ProviderInterface
{
    private const string MEDIA_IDENTIFIER = 'media';
    private const string CONTENT_STRUCTURE_IDENTIFIER = 'contentStructure';
    private const string USERS_IDENTIFIER = 'users';

    public function __construct(
        private ConfigResolverInterface $configResolver
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getConfig(): array
    {
        return [
            self::MEDIA_IDENTIFIER => $this->configResolver->getParameter('location_ids.media'),
            self::CONTENT_STRUCTURE_IDENTIFIER => $this->configResolver->getParameter('location_ids.content_structure'),
            self::USERS_IDENTIFIER => $this->configResolver->getParameter('location_ids.users'),
        ];
    }
}
