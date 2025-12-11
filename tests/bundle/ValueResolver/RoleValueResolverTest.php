<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\RoleValueResolver;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RoleValueResolverTest extends TestCase
{
    private RoleValueResolver $resolver;

    private MockObject&RoleService $roleService;

    protected function setUp(): void
    {
        $this->roleService = $this->createMock(RoleService::class);
        $this->resolver = new RoleValueResolver($this->roleService);
    }

    public function testResolve(): void
    {
        $role = $this->createMock(Role::class);
        $attributes = ['roleId' => '456'];

        $this->roleService->expects(self::once())
            ->method('loadRole')
            ->with(456)
            ->willReturn($role);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(Role::class);
        $argumentMetadata->method('getName')
            ->willReturn('role');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertCount(1, $result);
        self::assertSame($role, $result[0]);
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
            ->willReturn(Role::class);
        $argumentMetadata->method('getName')
            ->willReturn('role');

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
            'missing roleId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because roleId is missing',
            ],
            'invalid roleId type' => [
                'attributes' => ['roleId' => 'invalid'],
                'expectedMessage' => 'Should return empty because roleId is not numeric',
            ],
        ];
    }
}
