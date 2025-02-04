<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\SourceLanguageValueResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SourceLanguageValueResolverTest extends TestCase
{
    private SourceLanguageValueResolver $resolver;

    private MockObject&LanguageService $languageService;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
        $this->resolver = new SourceLanguageValueResolver($this->languageService);
    }

    public function testResolve(): void
    {
        $language = $this->createMock(Language::class);
        $attributes = ['fromLanguageCode' => 'fra-FR'];

        $this->languageService->expects(self::once())
            ->method('loadLanguage')
            ->with('fra-FR')
            ->willReturn($language);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(Language::class);
        $argumentMetadata->method('getName')
            ->willReturn('language');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertCount(1, $result);
        self::assertSame($language, $result[0]);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(Language::class);
        $argumentMetadata->method('getName')
            ->willReturn('language');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertSame([], $result, $expectedMessage);
    }

    /**
     * @return array<string, array{attributes: array<string, mixed>, expectedMessage: string}>
     */
    public static function invalidAttributesProvider(): array
    {
        return [
            'missing fromLanguageCode' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because fromLanguageCode is missing',
            ],
            'invalid fromLanguageCode type' => [
                'attributes' => ['fromLanguageCode' => 789],
                'expectedMessage' => 'Should return empty because fromLanguageCode is not a string',
            ],
        ];
    }
}
