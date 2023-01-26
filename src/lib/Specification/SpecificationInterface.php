<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Specification\SpecificationInterface as BaseSpecificationInterface;

/**
 * @deprecated 4.4.0 Use \Ibexa\Contracts\Core\Specification\SpecificationInterface
 */
interface SpecificationInterface extends BaseSpecificationInterface
{
}

class_alias(SpecificationInterface::class, 'EzSystems\EzPlatformAdminUi\Specification\SpecificationInterface');
