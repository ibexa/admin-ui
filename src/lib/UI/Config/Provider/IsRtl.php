<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\Rtl\RtlModeResolverInterface;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

final readonly class IsRtl implements ProviderInterface
{
    public function __construct(
        private RtlModeResolverInterface $rtlModeResolver,
    ) {
    }

    public function getConfig(): bool
    {
        return $this->rtlModeResolver->isRtl();
    }
}
