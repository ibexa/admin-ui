<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\RoleParamConverter;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = Role::class;
    public const PARAMETER_NAME = 'role';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\RoleParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(RoleService::class);

        $this->converter = new RoleParamConverter($this->serviceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $roleId The role identifier fetched from the request
     * @param int $roleIdToLoad The role identifier used to load the role
     */
    public function testApply($roleId, int $roleIdToLoad)
    {
        $valueObject = $this->createMock(Role::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRole')
            ->with($roleIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            RoleParamConverter::PARAMETER_ROLE_ID => $roleId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithWrongAttribute()
    {
        $requestAttributes = [
            RoleParamConverter::PARAMETER_ROLE_ID => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWhenNotFound()
    {
        $roleId = 42;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Role %s not found.', $roleId));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadRole')
            ->with($roleId)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            RoleParamConverter::PARAMETER_ROLE_ID => $roleId,
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

class_alias(RoleParamConverterTest::class, 'EzSystems\EzPlatformAdminUiBundle\Tests\ParamConverter\RoleParamConverterTest');
