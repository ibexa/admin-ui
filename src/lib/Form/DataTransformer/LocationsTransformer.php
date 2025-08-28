<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a Location's ID and a domain specific Location object.
 */
final readonly class LocationsTransformer implements DataTransformerInterface
{
    public function __construct(private LocationService $locationService)
    {
    }

    public function transform(mixed $value): ?string
    {
        /** TODO add sanity check is array of Location object? */
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return implode(',', array_column($value, 'id'));
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        $value = explode(',', $value);

        try {
            return array_map([$this->locationService, 'loadLocation'], $value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
