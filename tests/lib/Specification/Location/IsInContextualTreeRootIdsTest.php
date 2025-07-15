<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\Location\IsInContextualTreeRootIds;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class IsInContextualTreeRootIdsTest extends TestCase
{
    private const CONTEXTUAL_ROOT_IDS = [2, 5, 43, 55, 56, 67];

    /**
     * @covers \Ibexa\AdminUi\Specification\Location\IsInContextualTreeRootIds::isSatisfiedBy
     */
    public function testReturnsTrueWhenLocationIdIsInContextualRootList(): void
    {
        $specification = new IsInContextualTreeRootIds(
            $this->createConfigResolverReturning()
        );

        self::assertTrue(
            $specification->isSatisfiedBy($this->createLocationWithId(43))
        );
    }

    /**
     * @covers \Ibexa\AdminUi\Specification\Location\IsInContextualTreeRootIds::isSatisfiedBy
     */
    public function testReturnsFalseWhenLocationIdIsNotInContextualRootList(): void
    {
        $specification = new IsInContextualTreeRootIds(
            $this->createConfigResolverReturning()
        );

        self::assertFalse(
            $specification->isSatisfiedBy($this->createLocationWithId(999))
        );
    }

    private function createLocationWithId(int $id): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('getId')->willReturn($id);

        return $location;
    }

    private function createConfigResolverReturning(): ConfigResolverInterface
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->with('content_tree_module.contextual_tree_root_location_ids')
            ->willReturn(self::CONTEXTUAL_ROOT_IDS);

        return $configResolver;
    }
}
