<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Specification\OrSpecification as BaseOrSpecification;
use Ibexa\Contracts\Core\Specification\SpecificationInterface;

/**
 * @deprecated 4.4.0 Use \Ibexa\Contracts\Core\Specification\OrSpecification
 */
class OrSpecification extends AbstractSpecification
{
    private SpecificationInterface $baseSpecification;

    public function __construct(SpecificationInterface $one, SpecificationInterface $two)
    {
        $this->baseSpecification = new BaseOrSpecification($one, $two);
    }

    public function isSatisfiedBy($item): bool
    {
        return $this->baseSpecification->isSatisfiedBy($item);
    }
}
