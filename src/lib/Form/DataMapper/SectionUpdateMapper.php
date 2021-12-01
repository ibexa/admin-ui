<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between SectionUpdateStruct and SectionUpdateData objects.
 */
class SectionUpdateMapper implements DataMapperInterface
{
    /**
     * Maps given SectionUpdateStruct object to a SectionUpdateData object.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value
     *
     * @return \Ibexa\AdminUi\Form\Data\Section\SectionUpdateData
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject $value): SectionUpdateData
    {
        if (!$value instanceof SectionUpdateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . SectionUpdateStruct::class);
        }

        return new SectionUpdateData(new Section(['identifier' => $value->identifier, 'name' => $value->name]));
    }

    /**
     * Maps given SectionUpdateData object to a SectionUpdateStruct object.
     *
     * @param \Ibexa\AdminUi\Form\Data\Section\SectionUpdateData $data
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap($data): SectionUpdateStruct
    {
        if (!$data instanceof SectionUpdateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . SectionUpdateData::class);
        }

        return new SectionUpdateStruct([
            'name' => $data->getName(),
            'identifier' => $data->getIdentifier(),
        ]);
    }
}

class_alias(SectionUpdateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\SectionUpdateMapper');
