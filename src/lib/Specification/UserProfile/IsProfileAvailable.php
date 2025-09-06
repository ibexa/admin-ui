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
    public function __construct(
        private readonly UserProfileConfigurationInterface $userProfileConfiguration
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User $item
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        if ($this->userProfileConfiguration->isEnabled()) {
            return in_array(
                $item->getContentType()->getIdentifier(),
                $this->userProfileConfiguration->getContentTypes(),
                true
            );
        }

        return false;
    }
}
