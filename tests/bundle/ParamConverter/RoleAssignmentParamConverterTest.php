<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\RoleAssignmentParamConverter;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleAssignmentParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = RoleAssignment::class;
    public const PARAMETER_NAME = 'roleAssignment';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\RoleAssignmentParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(RoleService::class);

        $this->converter = new RoleAssignmentParamConverter($this->serviceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $roleAssignmentId The role assignment identifier fetched from the request
     * @param int $roleAssignmentIdToLoad The role assignment identifier used to load the role assignment
     */
    public function testApply($roleAssignmentId, int $roleAssignmentIdToLoad)
    {
        $valueObject = $this->createMock(RoleAssignment::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRoleAssignment')
            ->with($roleAssignmentIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            RoleAssignmentParamConverter::PRAMETER_ROLE_ASSIGNMENT_ID => $roleAssignmentId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithWrongAttribute()
    {
        $requestAttributes = [
            RoleAssignmentParamConverter::PRAMETER_ROLE_ASSIGNMENT_ID => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWhenNotFound()
    {
        $roleAssignmentId = 42;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Role assignment %s not found.', $roleAssignmentId));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRoleAssignment')
            ->with($roleAssignmentId)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            RoleAssignmentParamConverter::PRAMETER_ROLE_ASSIGNMENT_ID => $roleAssignmentId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    public function dataProvider(): array
    {
        return [
            'integer' => [42, 42],
            'number_as_string' => ['42', 42],
            'string' => ['42k', 42],
        ];
    }
}

class_alias(RoleAssignmentParamConverterTest::class, 'EzSystems\EzPlatformAdminUiBundle\Tests\ParamConverter\RoleAssignmentParamConverterTest');
