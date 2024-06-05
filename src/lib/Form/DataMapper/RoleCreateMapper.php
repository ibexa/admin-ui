<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Role\RoleCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Repository\Values\User\RoleCreateStruct;

/**
 * Maps between RoleCreateStruct and RoleCreateData objects.
 */
class RoleCreateMapper implements DataMapperInterface
{
    /**
     * Maps given RoleCreateStruct object to a RoleCreateData object.
     *
     * @param \Ibexa\Core\Repository\Values\User\RoleCreateStruct|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value
     *
     * @return \Ibexa\AdminUi\Form\Data\Role\RoleCreateData
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject $value): RoleCreateData
    {
        if (!$value instanceof RoleCreateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . RoleCreateStruct::class);
        }

        $data = new RoleCreateData();

        $data->setIdentifier($value->identifier);

        return $data;
    }

    /**
     * Maps given RoleCreateData object to a RoleCreateStruct object.
     *
     * @param \Ibexa\AdminUi\Form\Data\Role\RoleCreateData $data
     *
     * @return \Ibexa\Core\Repository\Values\User\RoleCreateStruct
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap($data): RoleCreateStruct
    {
        if (!$data instanceof RoleCreateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . RoleCreateData::class);
        }

        return new RoleCreateStruct([
            'identifier' => $data->getIdentifier(),
        ]);
    }
}
