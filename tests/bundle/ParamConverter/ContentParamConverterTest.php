<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\ContentParamConverter;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Symfony\Component\HttpFoundation\Request;

/**
 * Loads Content object using ids from request parameters.
 */
class ContentParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = Content::class;
    public const PARAMETER_NAME = 'content';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\ContentParamConverter */
    protected $converter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $contentServiceMock;

    protected function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);

        $this->converter = new ContentParamConverter($this->contentServiceMock);
    }

    public function testApply()
    {
        $contentId = 42;
        $languageCode = ['language_code'];
        $versionNo = 53;
        $valueObject = $this->createMock(Content::class);

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContent')
            ->with($contentId, $languageCode, $versionNo)
            ->willReturn($valueObject);

        $requestAttributes = [
            ContentParamConverter::PARAMETER_CONTENT_ID => $contentId,
            ContentParamConverter::PARAMETER_LANGUAGE_CODE => $languageCode,
            ContentParamConverter::PARAMETER_VERSION_NO => $versionNo,
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
     * @param $languageCode
     */
    public function testApplyWithWrongAttribute($contentId, $languageCode)
    {
        $versionNo = 53;

        $requestAttributes = [
            ContentParamConverter::PARAMETER_CONTENT_ID => $contentId,
            ContentParamConverter::PARAMETER_LANGUAGE_CODE => $languageCode,
            ContentParamConverter::PARAMETER_VERSION_NO => $versionNo,
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
            'empty_content_id' => [null, ['language_code']],
            'language_code_as_string' => [42, 'string'],
        ];
    }
}
