<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\Location\IsRoot;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsRoot::class)]
final class IsRootTest extends TestCase
{
    public function testReturnsTrueWhenLocationDepthIsOne(): void
    {
        $specification = new IsRoot();

        $location = $this->createLocationWithDepth(1);

        self::assertTrue($specification->isSatisfiedBy($location));
    }

    public function testReturnsFalseWhenLocationDepthIsNotOne(): void
    {
        $specification = new IsRoot();

        $location = $this->createLocationWithDepth(2);

        self::assertFalse($specification->isSatisfiedBy($location));
    }

    private function createLocationWithDepth(int $depth): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('getDepth')->willReturn($depth);

        return $location;
    }
}
