<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserProfile;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final class UserProfileConfiguration implements UserProfileConfigurationInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->configResolver->getParameter('user_profile.enabled');
    }

    /**
     * @return string[]
     */
    public function getFieldGroups(): array
    {
        return $this->configResolver->getParameter('user_profile.field_groups');
    }

    /**
     * @return string[]
     */
    public function getContentTypes(): array
    {
        return $this->configResolver->getParameter('user_profile.content_types');
    }
}
