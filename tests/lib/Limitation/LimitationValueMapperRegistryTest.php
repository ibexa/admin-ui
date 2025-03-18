<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation;

use Ibexa\AdminUi\Exception\ValueMapperNotFoundException;
use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationValueMapperRegistry;
use PHPUnit\Framework\TestCase;

class LimitationValueMapperRegistryTest extends TestCase
{
    public function testGetMappers(): void
    {
        $foo = $this->createMock(LimitationValueMapperInterface::class);
        $bar = $this->createMock(LimitationValueMapperInterface::class);

        $registry = new LimitationValueMapperRegistry([
            'foo' => $foo,
            'bar' => $bar,
        ]);

        $result = $registry->getMappers();

        self::assertCount(2, $result);
        self::assertContains($foo, $result);
        self::assertContains($bar, $result);
    }

    public function testGetMapper(): void
    {
        $foo = $this->createMock(LimitationValueMapperInterface::class);

        $registry = new LimitationValueMapperRegistry([
            'foo' => $foo,
        ]);

        self::assertEquals($foo, $registry->getMapper('foo'));
    }

    public function testGetNonExistingMapper(): void
    {
        $this->expectException(ValueMapperNotFoundException::class);

        $registry = new LimitationValueMapperRegistry([
            'foo' => $this->createMock(LimitationValueMapperInterface::class),
        ]);

        $registry->getMapper('bar');
    }

    public function testAddMapper(): void
    {
        $foo = $this->createMock(LimitationValueMapperInterface::class);

        $registry = new LimitationValueMapperRegistry();
        $registry->addMapper($foo, 'foo');

        self::assertTrue($registry->hasMapper('foo'));
    }

    public function testHasMapper(): void
    {
        $registry = new LimitationValueMapperRegistry([
            'foo' => $this->createMock(LimitationValueMapperInterface::class),
        ]);

        self::assertTrue($registry->hasMapper('foo'));
        self::assertFalse($registry->hasMapper('bar'));
    }
}
