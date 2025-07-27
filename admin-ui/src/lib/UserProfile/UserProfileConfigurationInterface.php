<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserProfile;

interface UserProfileConfigurationInterface
{
    public function isEnabled(): bool;

    /**
     * @return string[]
     */
    public function getFieldGroups(): array;

    /**
     * @return string[]
     */
    public function getContentTypes(): array;
}
