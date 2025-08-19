<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\LanguageTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LanguageTransformerTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService&\PHPUnit\Framework\MockObject\MockObject */
    private LanguageService $languageService;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
    }

    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(?Language $value, ?string $expected): void
    {
        $transformer = new LanguageTransformer($this->languageService);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $transformer = new LanguageTransformer($this->languageService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . Language::class . ' object.');

        $result = $transformer->transform($value);
        self::assertNull($result);
    }

    public function testReverseTransformWithLanguageCode(): void
    {
        $this->languageService
            ->expects(self::once())
            ->method('loadLanguage')
            ->with('eng-GB')
            ->willReturn(new Language(['languageCode' => 'eng-GB']));

        $transformer = new LanguageTransformer($this->languageService);

        $result = $transformer->reverseTransform('eng-GB');

        self::assertEquals(new Language(['languageCode' => 'eng-GB']), $result);
    }

    public function testReverseTransformWithNull(): void
    {
        $this->languageService
            ->expects(self::never())
            ->method('loadLanguageById');

        $transformer = new LanguageTransformer($this->languageService);

        $result = $transformer->reverseTransform(null);

        self::assertNull($result);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $this->languageService
            ->method('loadLanguage')
            ->will(self::throwException(new class('Language not found') extends NotFoundException {
            }));

        $transformer = new LanguageTransformer($this->languageService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Language not found');

        $transformer->reverseTransform('pol-PL');
    }

    /**
     * @return array<string, array{
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Language|null,
     *     string|null,
     * }>
     */
    public function transformDataProvider(): array
    {
        $language = new Language(['languageCode' => 'eng-GB']);

        return [
            'content_info_with_language_code' => [$language, 'eng-GB'],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function transformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }
}
