<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\RoleAssignmentValueResolver;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RoleAssignmentValueResolverTest extends TestCase
{
    private RoleAssignmentValueResolver $resolver;

    private MockObject&RoleService $roleService;

    protected function setUp(): void
    {
        $this->roleService = $this->createMock(RoleService::class);
        $this->resolver = new RoleAssignmentValueResolver($this->roleService);
    }

    public function testResolve(): void
    {
        $roleAssignment = $this->createMock(RoleAssignment::class);
        $attributes = ['roleAssignmentId' => '789'];

        $this->roleService->expects(self::once())
            ->method('loadRoleAssignment')
            ->with(789)
            ->willReturn($roleAssignment);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(RoleAssignment::class);
        $argumentMetadata->method('getName')
            ->willReturn('roleAssignment');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertCount(1, $result);
        self::assertSame($roleAssignment, $result[0]);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(RoleAssignment::class);
        $argumentMetadata->method('getName')
            ->willReturn('roleAssignment');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertSame([], $result, $expectedMessage);
    }

    /**
     * @return array<string, array{attributes: array<string, mixed>, expectedMessage: string}>
     */
    public static function invalidAttributesProvider(): array
    {
        return [
            'missing roleAssignmentId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because roleAssignmentId is missing',
            ],
            'invalid roleAssignmentId type' => [
                'attributes' => ['roleAssignmentId' => 'invalid'],
                'expectedMessage' => 'Should return empty because roleAssignmentId is not numeric',
            ],
        ];
    }
}
