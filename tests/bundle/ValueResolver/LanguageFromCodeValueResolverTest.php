<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\LanguageFromCodeValueResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class LanguageFromCodeValueResolverTest extends TestCase
{
    private LanguageFromCodeValueResolver $resolver;

    private MockObject&LanguageService $languageServiceMock;

    protected function setUp(): void
    {
        $this->languageServiceMock = $this->createMock(LanguageService::class);
        $this->resolver = new LanguageFromCodeValueResolver($this->languageServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(Language::class);

        $request = new Request([], [], [
            'languageCode' => 'pol-pl',
        ]);

        $mockLanguage = $this->createMock(Language::class);

        $this->languageServiceMock
            ->expects(self::once())
            ->method('loadLanguage')
            ->with('pol-pl')
            ->willReturn($mockLanguage);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockLanguage], $result);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(Language::class);

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([], $result, $expectedMessage);
    }

    /**
     * @phpstan-return array<array{attributes: array<string, mixed>, expectedMessage: string}>
     */
    public function invalidAttributesProvider(): array
    {
        return [
            'missing languageCode' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because languageCode is missing',
            ],
            'empty languageCode' => [
                'attributes' => ['languageCode' => ''],
                'expectedMessage' => 'Should return empty because languageCode is empty',
            ],
        ];
    }
}
