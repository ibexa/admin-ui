<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
final readonly class SectionUpdateMapper implements DataMapperInterface
{
    /**
     * Maps given SectionUpdateStruct object to a SectionUpdateData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject|SectionUpdateStruct $value): SectionUpdateData
    {
        if (!$value instanceof SectionUpdateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . SectionUpdateStruct::class);
        }

        return new SectionUpdateData(
            new Section(
                [
                    'identifier' => $value->identifier ?? '',
                    'name' => $value->name ?? '',
                ]
            )
        );
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap(mixed $data): SectionUpdateStruct
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
