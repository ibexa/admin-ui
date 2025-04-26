<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\RoleAssignmentTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment as APIRoleAsignment;
use Ibexa\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Core\Repository\Values\User\UserRoleAssignment as RoleAssignment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RoleAssignmentTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(?UserRoleAssignment $value, ?int $expected): void
    {
        $service = $this->createMock(RoleService::class);
        $transformer = new RoleAssignmentTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $roleService = $this->createMock(RoleService::class);
        $transformer = new RoleAssignmentTransformer($roleService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . APIRoleAsignment::class . ' object.');

        $transformer->transform($value);
    }

    public function testReverseTransformWithId(): void
    {
        $service = $this->createMock(RoleService::class);
        $service->expects(self::once())
            ->method('loadRoleAssignment')
            ->with(123456)
            ->willReturn(new RoleAssignment(['id' => 123456]));

        $transformer = new RoleAssignmentTransformer($service);

        $result = $transformer->reverseTransform(123456);

        self::assertEquals(new RoleAssignment(['id' => 123456]), $result);
    }

    public function testReverseTransformWithNull(): void
    {
        $service = $this->createMock(RoleService::class);
        $service->expects(self::never())
            ->method('loadRoleAssignment');

        $transformer = new RoleAssignmentTransformer($service);

        $result = $transformer->reverseTransform(null);

        self::assertNull($result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $service = $this->createMock(RoleService::class);

        $transformer = new RoleAssignmentTransformer($service);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $transformer->reverseTransform($value);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $service = $this->createMock(RoleService::class);
        $service->method('loadRoleAssignment')
            ->will(self::throwException(new class('Location not found') extends NotFoundException {
            }));

        $transformer = new RoleAssignmentTransformer($service);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Location not found');

        $transformer->reverseTransform(654321);
    }

    /**
     * @return array<string, array{UserRoleAssignment|null, int|null}>
     */
    public function transformDataProvider(): array
    {
        $transform = new RoleAssignment(['id' => 123456]);

        return [
            'with_id' => [$transform, 123456],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function transformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [new \stdClass()],
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
