<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\PolicyDraftValueResolver;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft;
use Ibexa\Contracts\Core\Repository\Values\User\RoleDraft;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PolicyDraftValueResolverTest extends TestCase
{
    private PolicyDraftValueResolver $resolver;

    private MockObject&RoleService $roleService;

    protected function setUp(): void
    {
        $this->roleService = $this->createMock(RoleService::class);
        $this->resolver = new PolicyDraftValueResolver($this->roleService);
    }

    public function testResolve(): void
    {
        $request = new Request([], [], [
            'roleId' => '1',
            'policyId' => '123',
        ]);

        $policyDraft = $this->createMock(PolicyDraft::class);
        $policyDraft->method('__get')->with('originalId')->willReturn(123);

        $roleDraft = $this->createMock(RoleDraft::class);
        $roleDraft->method('getPolicies')->willReturn([$policyDraft]);

        $this->roleService
            ->method('loadRoleDraftByRoleId')
            ->with(1)
            ->willReturn($roleDraft);

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(PolicyDraft::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertSame($policyDraft, $result[0]);
    }

    public function testResolveNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Policy draft 456 not found.');

        $request = new Request([], [], [
            'roleId' => '1',
            'policyId' => '456',
        ]);

        $policyDraft = $this->createMock(PolicyDraft::class);
        $policyDraft->method('__get')->with('originalId')->willReturn(123);

        $roleDraft = $this->createMock(RoleDraft::class);
        $roleDraft->method('getPolicies')->willReturn([$policyDraft]);

        $this->roleService
            ->method('loadRoleDraftByRoleId')
            ->with(1)
            ->willReturn($roleDraft);

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(PolicyDraft::class);

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
