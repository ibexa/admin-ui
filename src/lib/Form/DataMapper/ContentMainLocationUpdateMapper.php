<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between ContentMetadataUpdateStruct and ContentMetadataUpdateData objects.
 */
class ContentMainLocationUpdateMapper implements DataMapperInterface
{
    /**
     * Maps given ContentMetadataUpdateStruct object to a ContentMainLocationUpdateData object.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value
     *
     * @return \Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject $value): ContentMainLocationUpdateData
    {
        if (!$value instanceof ContentMetadataUpdateStruct) {
            throw new InvalidArgumentException('value', 'must be an instance of ' . ContentMetadataUpdateStruct::class);
        }

        $data = new ContentMainLocationUpdateData();

        $data->setLocation($value->mainLocationId);

        return $data;
    }

    /**
     * Maps given ContentMainLocationUpdateData object to a ContentMetadataUpdateStruct object.
     *
     * @param \Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData $data
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function reverseMap($data): ContentMetadataUpdateStruct
    {
        if (!$data instanceof ContentMainLocationUpdateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . ContentMainLocationUpdateData::class);
        }

        return new ContentMetadataUpdateStruct([
            'mainLocationId' => $data->getLocation()->id,
        ]);
    }
}

class_alias(ContentMainLocationUpdateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\ContentMainLocationUpdateMapper');
