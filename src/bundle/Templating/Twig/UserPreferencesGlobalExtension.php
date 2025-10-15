<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\User\UserSetting\UserSettingArrayAccessor;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * @internal
 *
 * @todo should be moved to ezplatform-user
 */
class UserPreferencesGlobalExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly UserSettingArrayAccessor $userSettingArrayAccessor
    ) {
    }

    /**
     * @return array<string, \ArrayAccess<string, mixed>>
     */
    public function getGlobals(): array
    {
        // has to use \ArrayAccess object due to BC promise
        return [
            'ibexa_user_settings' => $this->userSettingArrayAccessor,
        ];
    }
}
