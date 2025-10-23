<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content\Location;

use Ibexa\AdminUi\UI\Value;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final readonly class Mapper
{
    public function __construct(
        private ValueFactory $valueFactory
    ) {}

    /**
     * @param Location[] $locations
     *
     * @return Value\Content\Location[]
     */
    public function map(array $locations): array
    {
        $data = array_map(
            [$this->valueFactory, 'createLocation'],
            $locations
        );

        return $this->prioritizeMainLocation($data);
    }

    /**
     * @param Location[] $locations
     *
     * @return Value\Content\Location[]
     */
    private function prioritizeMainLocation(array $locations): array
    {
        $key = array_search(true, array_column($locations, 'main'));
        if ($key !== false) {
            $location = $locations[$key];
            unset($locations[$key]);
            array_unshift($locations, $location);
        }

        return $locations;
    }
}
