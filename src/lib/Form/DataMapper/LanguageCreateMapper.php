<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between LanguageCreateStruct and LanguageCreateData objects.
 */
final readonly class LanguageCreateMapper implements DataMapperInterface
{
    /**
     * Maps given LanguageCreateStruct object to a LanguageCreateData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject|LanguageCreateStruct $value): LanguageCreateData
    {
        if (!$value instanceof LanguageCreateStruct) {
            throw new InvalidArgumentException(
                'value',
                'must be an instance of ' . LanguageCreateStruct::class
            );
        }

        $data = new LanguageCreateData();
        $data->setName($value->name);
        $data->setLanguageCode($value->languageCode);
        $data->setEnabled($value->enabled);

        return $data;
    }

    /**
     * Maps given LanguageCreateData object to a LanguageCreateStruct object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap(mixed $data): LanguageCreateStruct
    {
        if (!$data instanceof LanguageCreateData) {
            throw new InvalidArgumentException(
                'data',
                'must be an instance of ' . LanguageCreateData::class
            );
        }

        return new LanguageCreateStruct([
            'languageCode' => $data->getLanguageCode(),
            'name' => $data->getName(),
            'enabled' => $data->isEnabled(),
        ]);
    }
}
