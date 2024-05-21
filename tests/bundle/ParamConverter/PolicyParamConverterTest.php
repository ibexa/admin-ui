<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\PolicyParamConverter;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Core\Repository\Values\User\Policy as UserPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PolicyParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = Policy::class;
    public const PARAMETER_NAME = 'policy';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\PolicyParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(RoleService::class);

        $this->converter = new PolicyParamConverter($this->serviceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $policyId The policy identifier fetched from the request
     * @param mixed $roleId The role identifier fetched from the request
     * @param int $roleIdToLoad The role identifier used to load the role
     */
    public function testApply($policyId, $roleId, int $roleIdToLoad)
    {
        $matchingPolicyId = 53;
        $valueObject = $this->createMock(Role::class);
        $valueObject->expects(self::once())
            ->method('getPolicies')
            ->willReturn([new UserPolicy(['id' => $matchingPolicyId]), new UserPolicy(['id' => 444])]);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRole')
            ->with($roleIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            PolicyParamConverter::PARAMETER_ROLE_ID => $roleId,
            PolicyParamConverter::PARAMETER_POLICY_ID => $policyId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        $policy = $request->attributes->get(self::PARAMETER_NAME);
        self::assertInstanceOf(self::SUPPORTED_CLASS, $policy);
        self::assertSame($matchingPolicyId, $policy->id);
    }

    /**
     * @dataProvider attributeProvider
     *
     * @param $roleId
     * @param $policyId
     */
    public function testApplyWithWrongAttribute($roleId, $policyId)
    {
        $requestAttributes = [
            PolicyParamConverter::PARAMETER_ROLE_ID => $roleId,
            PolicyParamConverter::PARAMETER_POLICY_ID => $policyId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWhenRoleNotFound()
    {
        $roleId = 42;
        $policyId = 53;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Role %s not found.', $roleId));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRole')
            ->with($roleId)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            PolicyParamConverter::PARAMETER_ROLE_ID => $roleId,
            PolicyParamConverter::PARAMETER_POLICY_ID => $policyId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    public function testApplyWhenPolicyNotFound()
    {
        $roleId = 42;
        $policyId = 53;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Policy %s not found.', $policyId));

        $valueObject = $this->createMock(Role::class);
        $valueObject->expects(self::once())
            ->method('getPolicies')
            ->willReturn([new UserPolicy(['id' => 123])]);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRole')
            ->with($roleId)
            ->willReturn($valueObject);

        $requestAttributes = [
            PolicyParamConverter::PARAMETER_ROLE_ID => $roleId,
            PolicyParamConverter::PARAMETER_POLICY_ID => $policyId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    /**
     * @return array
     */
    public function attributeProvider(): array
    {
        return [
            'empty_role_id' => [null, 53],
            'empty_policy_id' => [42, null],
        ];
    }

    public function dataProvider(): array
    {
        return [
            'integer' => [53, 42, 42],
            'number_as_string' => ['53', '42', 42],
            'string' => ['53k', '42k', 42],
        ];
    }
}
