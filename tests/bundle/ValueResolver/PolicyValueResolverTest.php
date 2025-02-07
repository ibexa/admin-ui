<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\PolicyValueResolver;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\RoleDraft;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PolicyValueResolverTest extends TestCase
{
    private PolicyValueResolver $resolver;

    private MockObject&RoleService $roleService;

    protected function setUp(): void
    {
        $this->roleService = $this->createMock(RoleService::class);
        $this->resolver = new PolicyValueResolver($this->roleService);
    }

    public function testResolve(): void
    {
        $policy = $this->createMock(Policy::class);
        $policy
            ->method('__get')
            ->with('id')
            ->willReturn(123);

        $role = $this->createMock(Role::class);
        $role
            ->method('getPolicies')
            ->willReturn([$policy]);

        $attributes = [
            'roleId' => '456',
            'policyId' => '123',
        ];

        $this->roleService->expects(self::once())
            ->method('loadRole')
            ->with(456)
            ->willReturn($role);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')->willReturn(Policy::class);
        $argumentMetadata->method('getName')->willReturn('policy');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertCount(1, $result);
        self::assertSame($policy, $result[0]);
    }

    public function testResolvePolicyNotFound(): void
    {
        $roleDraft = $this->createMock(RoleDraft::class);
        $roleDraft->method('getPolicies')->willReturn([]);

        $attributes = [
            'roleId' => '456',
            'policyId' => '999',
        ];

        $this->roleService->expects(self::once())
            ->method('loadRole')
            ->with(456)
            ->willReturn($roleDraft);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')->willReturn(PolicyDraft::class);
        $argumentMetadata->method('getName')->willReturn('policy');

        $request = new Request([], [], $attributes);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Policy draft 999 not found.');

        iterator_to_array($this->resolver->resolve($request, $argumentMetadata));
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')->willReturn(PolicyDraft::class);
        $argumentMetadata->method('getName')->willReturn('policy');

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
                'attributes' => ['policyId' => '123'],
                'expectedMessage' => 'Should return empty because roleId is missing',
            ],
            'missing policyId' => [
                'attributes' => ['roleId' => '456'],
                'expectedMessage' => 'Should return empty because policyId is missing',
            ],
            'invalid roleId type' => [
                'attributes' => ['roleId' => 'invalid', 'policyId' => '123'],
                'expectedMessage' => 'Should return empty because roleId is not numeric',
            ],
            'invalid policyId type' => [
                'attributes' => ['roleId' => '456', 'policyId' => 'invalid'],
                'expectedMessage' => 'Should return empty because policyId is not numeric',
            ],
        ];
    }
}
