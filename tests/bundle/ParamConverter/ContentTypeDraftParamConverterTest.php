<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeDraftParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ContentTypeDraftParamConverterTest extends AbstractParamConverterTest
{
    const SUPPORTED_CLASS = ContentTypeDraft::class;
    const PARAMETER_NAME = 'contentType';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeDraftParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $contentTypeServiceMock;

    protected function setUp(): void
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $this->converter = new ContentTypeDraftParamConverter($this->contentTypeServiceMock);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $contentTypeId The content type identifier fetched from the request
     * @param int $contentTypeIdToLoad The content type identifier used to load the Content Type draft
     */
    public function testApply($contentTypeId, int $contentTypeIdToLoad)
    {
        $valueObject = $this->createMock(ContentTypeDraft::class);

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentTypeDraft')
            ->with($contentTypeIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            ContentTypeDraftParamConverter::PARAMETER_CONTENT_TYPE_ID => $contentTypeId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithWrongAttribute()
    {
        $requestAttributes = [
            ContentTypeDraftParamConverter::PARAMETER_CONTENT_TYPE_ID => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->assertFalse($this->converter->apply($request, $config));
        $this->assertNull($request->attributes->get(self::PARAMETER_NAME));
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

class_alias(ContentTypeDraftParamConverterTest::class, 'EzSystems\EzPlatformAdminUiBundle\Tests\ParamConverter\ContentTypeDraftParamConverterTest');
