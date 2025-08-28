<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Maps between ContentMetadataUpdateStruct and ContentMetadataUpdateData objects.
 */
final readonly class ContentMainLocationUpdateMapper implements DataMapperInterface
{
    public function __construct(private LocationService $locationService)
    {
    }

    /**
     * Maps given ContentMetadataUpdateStruct object to a ContentMainLocationUpdateData object.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function map(ValueObject|ContentMetadataUpdateStruct $value): ContentMainLocationUpdateData
    {
        if (!$value instanceof ContentMetadataUpdateStruct) {
            throw new InvalidArgumentException(
                'value',
                'must be an instance of ' . ContentMetadataUpdateStruct::class
            );
        }

        $data = new ContentMainLocationUpdateData();

        if (null !== $value->mainLocationId) {
            $data->setLocation($this->locationService->loadLocation($value->mainLocationId));
        }

        return $data;
    }

    /**
     * Maps given ContentMainLocationUpdateData object to a ContentMetadataUpdateStruct object.
     *
     * @param \Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData $data
     */
    public function reverseMap(mixed $data): ContentMetadataUpdateStruct
    {
        return new ContentMetadataUpdateStruct([
            'mainLocationId' => $data->getLocation()?->getId(),
        ]);
    }
}
