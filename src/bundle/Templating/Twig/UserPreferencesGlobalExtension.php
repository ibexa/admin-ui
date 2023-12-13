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
    /** @var \Ibexa\User\UserSetting\UserSettingArrayAccessor */
    protected $userSettingArrayAccessor;

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
            /** @deprecated ez_user_settings is deprecated since 4.0, use ibexa_user_settings instead */
            'ez_user_settings' => $this->userSettingArrayAccessor,
            'ibexa_user_settings' => $this->userSettingArrayAccessor,
        ];
    }
}

class_alias(UserPreferencesGlobalExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\UserPreferencesGlobalExtension');
