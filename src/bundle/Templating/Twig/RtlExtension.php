<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\AdminUi\Rtl\RtlModeResolverInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RtlExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RtlModeResolverInterface $rtlModeResolver,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'ibexa_is_rtl' => $this->rtlModeResolver->isRtl(),
        ];
    }
}
