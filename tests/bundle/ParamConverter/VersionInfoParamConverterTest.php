<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\VersionInfoParamConverter;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Symfony\Component\HttpFoundation\Request;

class VersionInfoParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = VersionInfo::class;
    public const PARAMETER_NAME = 'versionInfo';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\VersionInfoParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(ContentService::class);

        $this->converter = new VersionInfoParamConverter($this->serviceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $versionNo
     * @param int $versionNoToload
     * @param mixed $contentId
     * @param int $contentIdToLoad
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testApply($versionNo, int $versionNoToload, $contentId, int $contentIdToLoad)
    {
        $valueObject = $this->createMock(ContentInfo::class);
        $versionInfo = $this->createMock(VersionInfo::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentInfo')
            ->with($contentIdToLoad)
            ->willReturn($valueObject);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadVersionInfo')
            ->with($valueObject, $versionNoToload)
            ->willReturn($versionInfo);

        $requestAttributes = [
            VersionInfoParamConverter::PARAMETER_CONTENT_ID => $contentId,
            VersionInfoParamConverter::PARAMETER_VERSION_NO => $versionNo,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    /**
     * @dataProvider attributeProvider
     *
     * @param $contentId
     * @param $versionNo
     */
    public function testApplyWithWrongAttribute($contentId, $versionNo)
    {
        $requestAttributes = [
            VersionInfoParamConverter::PARAMETER_CONTENT_ID => $contentId,
            VersionInfoParamConverter::PARAMETER_VERSION_NO => $versionNo,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    /**
     * @return array
     */
    public function attributeProvider(): array
    {
        return [
            'empty_content_id' => [null, 53],
            'empty_version_no' => [42, null],
        ];
    }

    public function dataProvider(): array
    {
        return [
            'integer' => [53, 53, 42, 42],
            'number_as_string' => ['53', 53, '42', 42],
            'string' => ['53k', 53, '42k', 42],
        ];
    }
}

class_alias(VersionInfoParamConverterTest::class, 'EzSystems\EzPlatformAdminUiBundle\Tests\ParamConverter\VersionInfoParamConverterTest');
