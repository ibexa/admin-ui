<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Exception;

use Exception;
use RuntimeException;

final class ContentTypeIconNotFoundException extends RuntimeException
{
    public function __construct(string $contentType, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct("No icon found for '$contentType' content type", $code, $previous);
    }
}
