<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeParamConverter;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTypeParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = ContentType::class;
    public const PARAMETER_NAME = 'contentType';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\ContentTypeParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(ContentTypeService::class);

        $userLanguagePreferenceProvider = $this->createMock(UserLanguagePreferenceProviderInterface::class);
        $this->converter = new ContentTypeParamConverter($this->serviceMock, $userLanguagePreferenceProvider);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $contentTypeId The content type identifier fetched from the request
     * @param int $contentTypeIdToLoad The content type identifier used to load the content type draft
     */
    public function testApplyId($contentTypeId, int $contentTypeIdLoad)
    {
        $valueObject = $this->createMock(ContentType::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentType')
            ->with($contentTypeIdLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_ID => $contentTypeId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyIdWithWrongValue()
    {
        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_ID => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyIdWhenNotFound()
    {
        $contentTypeId = 42;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Content type %s not found.', $contentTypeId));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentType')
            ->with($contentTypeId)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_ID => $contentTypeId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    public function testApplyIdentifier()
    {
        $contentTypeIdentifier = 'test_identifier';
        $valueObject = $this->createMock(ContentType::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with($contentTypeIdentifier)
            ->willReturn($valueObject);

        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_IDENTIFIER => $contentTypeIdentifier,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);

        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyIdentifierWithWrongValue()
    {
        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_IDENTIFIER => null,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyIdentifierWhenNotFound()
    {
        $contentTypeIdentifier = 'test_identifier';

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Content type %s not found.', $contentTypeIdentifier));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with($contentTypeIdentifier)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            ContentTypeParamConverter::PARAMETER_CONTENT_TYPE_IDENTIFIER => $contentTypeIdentifier,
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
