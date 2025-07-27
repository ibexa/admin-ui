<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Role\RoleCopyData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Repository\Values\User\RoleCopyStruct;

/**
 * Maps between RoleCopyStruct and RoleCopyData objects.
 */
class RoleCopyMapper implements DataMapperInterface
{
    /**
     * Maps given RoleCopyStruct object to a RoleCopyData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject $value): RoleCopyData
    {
        if (!$value instanceof RoleCopyStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . RoleCopyStruct::class);
        }

        return new RoleCopyData($value->role);
    }

    /**
     * Maps given RoleCopyData object to a RoleCopyStruct object.
     *
     * @param \Ibexa\AdminUi\Form\Data\Role\RoleCopyData $data
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap($data): RoleCopyStruct
    {
        if (!$data instanceof RoleCopyData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . RoleCopyData::class);
        }

        return new RoleCopyStruct([
            'newIdentifier' => $data->getNewIdentifier(),
        ]);
    }
}
