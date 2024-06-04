<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeGroupParamConverter;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTypeGroupParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = ContentTypeGroup::class;
    public const PARAMETER_NAME = 'contentTypeGroup';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeGroupParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(ContentTypeService::class);

        $this->converter = new ContentTypeGroupParamConverter($this->serviceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $contentTypeGroupId The identifier fetched from the request
     * @param int $contentTypeGroupIdToLoad The identifier used to load the content type Group
     */
    public function testApply($contentTypeGroupId, int $contentTypeGroupIdToLoad)
    {
        $valueObject = $this->createMock(ContentTypeGroup::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentTypeGroup')
            ->with($contentTypeGroupIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            ContentTypeGroupParamConverter::PARAMETER_CONTENT_TYPE_GROUP_ID => $contentTypeGroupId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithWrongAttribute()
    {
        $requestAttributes = [
            ContentTypeGroupParamConverter::PARAMETER_CONTENT_TYPE_GROUP_ID => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWhenNotFound()
    {
        $contentTypeGroupId = 42;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            sprintf('Content type group %s not found.', $contentTypeGroupId)
        );

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentTypeGroup')
            ->with($contentTypeGroupId)
            ->willThrowException(
                $this->createMock(NotFoundException::class)
            );

        $requestAttributes = [
            ContentTypeGroupParamConverter::PARAMETER_CONTENT_TYPE_GROUP_ID => $contentTypeGroupId,
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
