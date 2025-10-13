<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class FocusModeChangedEvent extends Event
{
    public function __construct(private readonly bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
