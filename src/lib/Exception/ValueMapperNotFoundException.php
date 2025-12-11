<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Exception;

use Exception;
use InvalidArgumentException;

final class ValueMapperNotFoundException extends InvalidArgumentException
{
    public function __construct($limitationType, $code = 0, ?Exception $previous = null)
    {
        parent::__construct("No LimitationValueMapper found for '$limitationType'", $code, $previous);
    }
}
