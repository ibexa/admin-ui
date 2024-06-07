<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

class MainTranslationUpdateMapper implements DataMapperInterface
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value
     *
     * @return \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData
     */
    public function map(ValueObject $value)
    {
        if (!$value instanceof ContentMetadataUpdateStruct) {
            throw new InvalidArgumentException('value', sprintf('must be an instance of %s', ContentMetadataUpdateStruct::class));
        }

        $data = new MainTranslationUpdateData();
        $data->setLanguageCode($value->mainLanguageCode);

        return $data;
    }

    /**
     * @param \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData $data
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public function reverseMap($data)
    {
        if (!$data instanceof MainTranslationUpdateData) {
            throw new InvalidArgumentException('value', sprintf('must be an instance of %s', MainTranslationUpdateData::class));
        }

        return new ContentMetadataUpdateStruct([
            'mainLanguageCode' => $data->getLanguageCode(),
            'name' => $data->getContent()->getName($data->getLanguageCode()),
        ]);
    }
}

class_alias(MainTranslationUpdateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\MainTranslationUpdateMapper');
