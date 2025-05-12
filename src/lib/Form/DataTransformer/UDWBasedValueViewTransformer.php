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
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<Location[], string>
 */
class UDWBasedValueViewTransformer implements DataTransformerInterface
{
    public const string DELIMITER = ',';

    private LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function transform(mixed $value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        return implode(self::DELIMITER, array_column($value, 'id'));
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]|null
     */
    public function reverseTransform(mixed $value): ?array
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $ids = explode(self::DELIMITER, $value);
        $ids = array_map('intval', $ids);

        try {
            return array_map($this->locationService->loadLocation(...), $ids);
        } catch (NotFoundException | UnauthorizedException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
