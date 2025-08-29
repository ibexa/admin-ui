<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Exception;

use Ibexa\Contracts\Core\Repository\Exceptions\Exception as RepositoryException;
use RuntimeException;

final class UnresolvedPreviewUrlException extends RuntimeException implements RepositoryException
{
}
