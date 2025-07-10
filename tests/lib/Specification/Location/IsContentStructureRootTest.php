<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\Location\IsContentStructureRoot;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class IsContentStructureRootTest extends TestCase
{
    /**
     * @covers \Ibexa\AdminUi\Specification\Location\IsContentStructureRoot::isSatisfiedBy
     */
    public function testReturnsTrueWhenLocationDepthMatchesRoot(): void
    {
        $id = 1;

        $specification = new IsContentStructureRoot(
            $this->createConfigResolverReturning($id)
        );

        self::assertTrue(
            $specification->isSatisfiedBy($this->createLocationWithId($id))
        );
    }

    /**
     * @covers \Ibexa\AdminUi\Specification\Location\IsContentStructureRoot::isSatisfiedBy
     */
    public function testReturnsFalseWhenLocationDepthDoesNotMatchRoot(): void
    {
        $specification = new IsContentStructureRoot(
            $this->createConfigResolverReturning(1)
        );

        self::assertFalse(
            $specification->isSatisfiedBy($this->createLocationWithId(3))
        );
    }

    private function createLocationWithId(int $id): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('getId')->willReturn($id);

        return $location;
    }

    private function createConfigResolverReturning(int $id): ConfigResolverInterface
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->with('location_ids.content_structure')
            ->willReturn($id);

        return $configResolver;
    }
}
