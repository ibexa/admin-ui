<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\Bundle\AdminUi\Templating\Twig\LocationExtension;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Twig\Test\IntegrationTestCase;

final class LocationExtensionTest extends IntegrationTestCase
{
    protected function getExtensions(): array
    {
        return [
            new LocationExtension(),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/location/';
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocationWithAllPossibleSortFields(): array
    {
        return array_map(
            [$this, 'createLocationWithSortField'],
            array_keys(Location::SORT_FIELD_MAP)
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createLocationWithSortField(int $field): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('__get')->with('sortField')->willReturn($field);

        return $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createLocationWithSortOrder(int $order): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('__get')->with('sortOrder')->willReturn($order);

        return $location;
    }
}
