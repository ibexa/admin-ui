<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Request\Resolver;

use Ibexa\AdminUi\Request\Resolver\AdminUiContentLanguageCodeResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class AdminUiContentLanguageCodeResolverTest extends TestCase
{
    private AdminUiContentLanguageCodeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AdminUiContentLanguageCodeResolver();
    }

    /**
     * @dataProvider provideResolvableLanguageContext
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolvesLanguageCode(array $attributes, string $expectedLanguageCode): void
    {
        $request = new Request([], [], $attributes);

        self::assertSame($expectedLanguageCode, $this->resolver->resolve($request));
    }

    /**
     * @dataProvider provideUnresolvableLanguageContext
     *
     * @param array<string, mixed> $attributes
     */
    public function testReturnsNullForUnresolvableLanguageContext(array $attributes): void
    {
        self::assertNull($this->resolver->resolve(new Request([], [], $attributes)));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public function provideResolvableLanguageContext(): iterable
    {
        yield 'language object' => [
            ['language' => new Language(['languageCode' => 'eng-GB'])],
            'eng-GB',
        ];
        yield 'language string' => [
            ['language' => 'ger-DE'],
            'ger-DE',
        ];
        yield 'languageCode string' => [
            ['languageCode' => 'pol-PL'],
            'pol-PL',
        ];
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public function provideUnresolvableLanguageContext(): iterable
    {
        yield 'missing context' => [[]];
        yield 'empty context' => [['language' => '', 'languageCode' => '']];
    }
}
