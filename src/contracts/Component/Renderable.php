<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Component;

use Ibexa\Contracts\TwigComponents\ComponentInterface;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\Contracts\AdminUi\Component\Renderable} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\ComponentInterface} instead
 */
interface Renderable extends ComponentInterface
{
}

class_alias(Renderable::class, 'EzSystems\EzPlatformAdminUi\Component\Renderable');
