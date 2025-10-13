<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between SectionCreateStruct and SectionCreateData objects.
 */
final readonly class SectionCreateMapper implements DataMapperInterface
{
    /**
     * Maps given SectionCreateStruct object to a SectionCreateData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject|SectionCreateStruct $value): SectionCreateData
    {
        if (!$value instanceof SectionCreateStruct) {
            throw new InvalidArgumentException(
                'value',
                'must be an instance of ' . SectionCreateStruct::class
            );
        }

        return new SectionCreateData($value->identifier, $value->name);
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap(mixed $data): SectionCreateStruct
    {
        if (!$data instanceof SectionCreateData) {
            throw new InvalidArgumentException(
                'data',
                'must be an instance of ' . SectionCreateData::class
            );
        }

        return new SectionCreateStruct([
            'name' => $data->getName(),
            'identifier' => $data->getIdentifier(),
        ]);
    }
}
