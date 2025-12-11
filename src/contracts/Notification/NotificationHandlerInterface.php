<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Notification;

interface NotificationHandlerInterface
{
    public function info(string $message): void;

    public function success(string $message): void;

    public function warning(string $message): void;

    public function error(string $message): void;
}
