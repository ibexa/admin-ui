<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a Location's ID and a domain specific object.
 */
class LocationTransformer implements DataTransformerInterface
{
    /** @var LocationService */
    protected $locationService;

    /**
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Transforms a domain specific Location object into a Location's identifier.
     *
     * @param mixed $value
     *
     * @return int|null
     *
     * @throws TransformationFailedException
     */
    public function transform($value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof APILocation) {
            throw new TransformationFailedException('Expected a ' . APILocation::class . ' object.');
        }

        return $value->id;
    }

    /**
     * Transforms a Location's ID into a domain specific Location object.
     *
     * @param mixed $value
     *
     * @return Location|null
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($value): ?APILocation
    {
        if (empty($value)) {
            return null;
        }

        try {
            return $this->locationService->loadLocation((int)$value);
        } catch (NotFoundException | UnauthorizedException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

class_alias(LocationTransformer::class, 'EzSystems\EzPlatformAdminUi\Form\DataTransformer\LocationTransformer');
