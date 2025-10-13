<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\UI\Action;

interface EventDispatcherInterface
{
    public const string EVENT_NAME_PREFIX = 'ibexa.admin_ui.action';

    public function dispatch(UiActionEventInterface $event): void;
}
