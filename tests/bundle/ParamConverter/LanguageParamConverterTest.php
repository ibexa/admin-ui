<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LanguageParamConverterTest extends AbstractParamConverterTest
{
    public const SUPPORTED_CLASS = Language::class;
    public const PARAMETER_NAME = 'language';

    /** @var \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter */
    protected $converter;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService|\PHPUnit\Framework\MockObject\MockObject */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(LanguageService::class);
        $this->converter = new LanguageParamConverter($this->serviceMock);
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::apply
     *
     * @dataProvider dataProvider
     *
     * @param mixed $languageId The language identifier fetched from the request
     * @param int $languageIdToLoad The language identifier used to load the language
     */
    public function testApplyForLanguageId($languageId, int $languageIdToLoad)
    {
        $valueObject = $this->createMock(Language::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadLanguageById')
            ->with($languageIdToLoad)
            ->willReturn($valueObject);

        $requestAttributes = [
            LanguageParamConverter::PARAMETER_LANGUAGE_ID => $languageId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertTrue($this->converter->apply($request, $config));
        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::apply
     */
    public function testApplyForLanguageCode()
    {
        $languageCode = 'eng-GB';
        $valueObject = $this->createMock(Language::class);

        $this->serviceMock
            ->expects(self::once())
            ->method('loadLanguage')
            ->with($languageCode)
            ->willReturn($valueObject);

        $request = new Request([], [], [
            LanguageParamConverter::PARAMETER_LANGUAGE_CODE => $languageCode,
        ]);

        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);

        self::assertInstanceOf(self::SUPPORTED_CLASS, $request->attributes->get(self::PARAMETER_NAME));
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::apply
     *
     * @dataProvider dataProviderForApplyWithWrongAttribute
     */
    public function testApplyWithWrongAttribute(array $attributes)
    {
        $request = new Request([], [], $attributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get(self::PARAMETER_NAME));
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::apply
     */
    public function testApplyWithNonExistingLanguageId()
    {
        $languageId = 42;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Language %s not found.', $languageId));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadLanguageById')
            ->with($languageId)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            LanguageParamConverter::PARAMETER_LANGUAGE_ID => $languageId,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::apply
     */
    public function testApplyWithNonExistingLanguageCode()
    {
        $languageCode = 'eng-Gb';

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Language %s not found.', $languageCode));

        $this->serviceMock
            ->expects(self::once())
            ->method('loadLanguage')
            ->with($languageCode)
            ->willThrowException($this->createMock(NotFoundException::class));

        $requestAttributes = [
            LanguageParamConverter::PARAMETER_LANGUAGE_CODE => $languageCode,
        ];

        $request = new Request([], [], $requestAttributes);
        $config = $this->createConfiguration(self::SUPPORTED_CLASS, self::PARAMETER_NAME);

        $this->converter->apply($request, $config);
    }

    /**
     * @covers \Ibexa\Bundle\AdminUi\ParamConverter\LanguageParamConverter::supports
     *
     * @dataProvider dataProviderForSupport
     */
    public function testSupport(string $class, bool $expected)
    {
        self::assertEquals($expected, $this->converter->supports($this->createConfiguration($class)));
    }

    public function dataProviderForSupport(): array
    {
        return [
            [self::SUPPORTED_CLASS, true],
            [stdClass::class, false],
        ];
    }

    public function dataProviderForApplyWithWrongAttribute(): array
    {
        return [
            [
                [LanguageParamConverter::PARAMETER_LANGUAGE_ID => null],
            ],
            [
                [LanguageParamConverter::PARAMETER_LANGUAGE_CODE => null],
            ],
            [
                [],
            ],
        ];
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

class_alias(LanguageParamConverterTest::class, 'EzSystems\EzPlatformAdminUiBundle\Tests\ParamConverter\LanguageParamConverterTest');
