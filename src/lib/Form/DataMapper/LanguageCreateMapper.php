<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between LanguageCreateStruct and LanguageCreateData objects.
 */
class LanguageCreateMapper implements DataMapperInterface
{
    /**
     * Maps given LanguageCreateStruct object to a LanguageCreateData object.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value
     *
     * @return \Ibexa\AdminUi\Form\Data\Language\LanguageCreateData
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject $value): LanguageCreateData
    {
        if (!$value instanceof LanguageCreateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . LanguageCreateStruct::class);
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
     * @param \Ibexa\AdminUi\Form\Data\Language\LanguageCreateData $data
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap($data): LanguageCreateStruct
    {
        if (!$data instanceof LanguageCreateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . LanguageCreateData::class);
        }

        return new LanguageCreateStruct([
            'languageCode' => $data->getLanguageCode(),
            'name' => $data->getName(),
            'enabled' => $data->isEnabled(),
        ]);
    }
}

class_alias(LanguageCreateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\LanguageCreateMapper');
