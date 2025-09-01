<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

/**
 * Interface for Limitation Value mappers.
 */
interface LimitationValueMapperInterface
{
    /**
     * Map the limitation values, in order to pass them as context of limitation value rendering.
     *
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation): array;
}
