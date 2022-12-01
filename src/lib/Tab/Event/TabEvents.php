<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

class TabEvents
{
    /**
     * Happens just after tabs group creation.
     */
    public const TAB_GROUP_INITIALIZE = 'ezplatform.tab.group.initialize';

    /**
     * Happens just before rendering tabs group.
     */
    public const TAB_GROUP_PRE_RENDER = 'ezplatform.tab.group.pre_render';

    /**
     * Happens just before rendering tab.
     */
    public const TAB_PRE_RENDER = 'ezplatform.tab.pre_render';

    /**
     * Is dispatched on tabs extending AbstractEventDispatchingTab.
     *
     * Allows to manipulate template path and parameters before rendering by Twig.
     */
    public const TAB_RENDER = 'ezplatform.tab.render';
}

class_alias(TabEvents::class, 'EzSystems\EzPlatformAdminUi\Tab\Event\TabEvents');
