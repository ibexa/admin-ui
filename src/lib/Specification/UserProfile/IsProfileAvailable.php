<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\UserProfile;

use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class IsProfileAvailable extends AbstractSpecification
{
    private UserProfileConfigurationInterface $userProfileConfiguration;

    public function __construct(UserProfileConfigurationInterface $userProfileConfiguration)
    {
        $this->userProfileConfiguration = $userProfileConfiguration;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User $item
     */
    public function isSatisfiedBy($item): bool
    {
        if ($this->userProfileConfiguration->isEnabled()) {
            return in_array(
                $item->getContentType()->identifier,
                $this->userProfileConfiguration->getContentTypes(),
                true
            );
        }

        return false;
    }
}
