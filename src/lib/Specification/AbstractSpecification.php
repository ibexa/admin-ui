<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Specification\AbstractSpecification as BaseAbstractSpecification;

/**
 * @deprecated 4.4.0 Use \Ibexa\Contracts\Core\Specification\AbstractSpecification
 */
abstract class AbstractSpecification extends BaseAbstractSpecification
{
}

class_alias(AbstractSpecification::class, 'EzSystems\EzPlatformAdminUi\Specification\AbstractSpecification');
