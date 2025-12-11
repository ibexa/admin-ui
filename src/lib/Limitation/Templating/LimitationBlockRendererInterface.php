<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Templating;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

interface LimitationBlockRendererInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function renderLimitationValue(Limitation $limitation, array $parameters = []): string;
}
