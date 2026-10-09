<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between SectionCreateStruct and SectionCreateData objects.
 */
class SectionCreateMapper implements DataMapperInterface
{
    /**
     * Maps given SectionCreateStruct object to a SectionCreateData object.
     *
     * @param SectionCreateStruct|ValueObject $value
     *
     * @return SectionCreateData
     *
     * @throws InvalidArgumentException
     */
    public function map(ValueObject $value): SectionCreateData
    {
        if (!$value instanceof SectionCreateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . SectionCreateStruct::class);
        }

        return new SectionCreateData($value->identifier, $value->name);
    }

    /**
     * Maps given SectionCreateData object to a SectionCreateStruct object.
     *
     * @param SectionCreateData $data
     *
     * @return SectionCreateStruct
     *
     * @throws InvalidArgumentException
     */
    public function reverseMap($data): SectionCreateStruct
    {
        if (!$data instanceof SectionCreateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . SectionCreateData::class);
        }

        return new SectionCreateStruct([
            'name' => $data->getName(),
            'identifier' => $data->getIdentifier(),
        ]);
    }
}

class_alias(SectionCreateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\SectionCreateMapper');
