<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\RoleTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Core\Repository\Values\User\Role;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RoleTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(?Role $value, ?int $expected): void
    {
        $service = $this->createMock(RoleService::class);
        $transformer = new RoleTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    public function testReverseTransformWithId(): void
    {
        $service = $this->createMock(RoleService::class);
        $service->expects(self::once())
            ->method('loadRole')
            ->with(123456)
            ->willReturn(new Role(['id' => 123456]));

        $transformer = new RoleTransformer($service);

        $result = $transformer->reverseTransform(123456);

        self::assertEquals(new Role(['id' => 123456]), $result);
    }

    public function testReverseTransformWithNull(): void
    {
        $service = $this->createMock(RoleService::class);
        $service->expects(self::never())
            ->method('loadRole');

        $transformer = new RoleTransformer($service);

        $result = $transformer->reverseTransform(null);

        self::assertNull($result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $roleService = $this->createMock(RoleService::class);
        $transformer = new RoleTransformer($roleService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $transformer->reverseTransform($value);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Location not found');

        $service = $this->createMock(RoleService::class);
        $service->method('loadRole')
            ->will(self::throwException(new class('Location not found') extends NotFoundException {
            }));

        $transformer = new RoleTransformer($service);

        $transformer->reverseTransform(654321);
    }

    /**
     * @return array<string, array{\Ibexa\Core\Repository\Values\User\Role|null, int|null}>
     */
    public function transformDataProvider(): array
    {
        $transform = new Role(['id' => 123456]);

        return [
            'with_id' => [$transform, 123456],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function reverseTransformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[1]],
            'object' => [new \stdClass()],
            'scientific_notation' => ['1337e0'],
            'hexadecimal' => ['0x539'],
        ];
    }
}
