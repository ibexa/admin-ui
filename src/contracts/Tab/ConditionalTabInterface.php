<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Tab;

/**
 * Conditional Tab interface needs to be implemented by tabs,
 * which needs to be evaluated depending on the context.
 */
interface ConditionalTabInterface
{
    /**
     * Get information about tab presence.
     *
     * @param array<string, mixed> $parameters
     */
    public function evaluate(array $parameters): bool;
}

class_alias(ConditionalTabInterface::class, 'EzSystems\EzPlatformAdminUi\Tab\ConditionalTabInterface');
