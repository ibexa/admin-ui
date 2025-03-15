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
    protected UserSettingArrayAccessor $userSettingArrayAccessor;

    /**
     * @param \Ibexa\User\UserSetting\UserSettingArrayAccessor $userSettingArrayAccessor
     */
    public function __construct(
        UserSettingArrayAccessor $userSettingArrayAccessor
    ) {
        $this->userSettingArrayAccessor = $userSettingArrayAccessor;
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        // has to use \ArrayAccess object due to BC promise
        return [
            'ibexa_user_settings' => $this->userSettingArrayAccessor,
        ];
    }
}
