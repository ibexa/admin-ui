<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Form\DataMapper;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Data Mapper provide interface to bidirectional transfer of data between a Struct objects and a Data objects.
 */
interface DataMapperInterface
{
    public function map(ValueObject $value): mixed;

    public function reverseMap(mixed $data): mixed;
}
