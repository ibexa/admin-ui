<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Resolver;

interface IconPathResolverInterface
{
    public function resolve(string $icon, ?string $set = null): string;
}

class_alias(IconPathResolverInterface::class, 'Ibexa\Platform\Assets\Resolver\IconPathResolverInterface');
