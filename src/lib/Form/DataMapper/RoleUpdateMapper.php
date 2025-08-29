<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Role\RoleUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between RoleUpdateStruct and RoleUpdateData objects.
 */
final readonly class RoleUpdateMapper implements DataMapperInterface
{
    /**
     * Maps given RoleUpdateStruct object to a RoleUpdateData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject|RoleUpdateStruct $value): RoleUpdateData
    {
        if (!$value instanceof RoleUpdateStruct) {
            throw new InvalidArgumentException(
                'value',
                'must be an instance of ' . RoleUpdateStruct::class
            );
        }

        $data = new RoleUpdateData();

        $data->setIdentifier($value->identifier);

        return $data;
    }

    public function reverseMap(mixed $data): RoleUpdateStruct
    {
        if (!$data instanceof RoleUpdateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . RoleUpdateData::class);
        }

        return new RoleUpdateStruct([
            'identifier' => $data->getIdentifier(),
        ]);
    }
}
