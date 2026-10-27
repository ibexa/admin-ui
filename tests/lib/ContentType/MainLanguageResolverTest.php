<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\ContentType;

use Ibexa\AdminUi\ContentType\MainLanguageResolver;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\AdminUi\ContentType\MainLanguageResolver
 */
final class MainLanguageResolverTest extends TestCase
{
    /**
     * @dataProvider provideForResolve
     *
     * @param string[] $configuredLanguageCodes
     * @param string[] $existingLanguageCodes
     */
    public function testResolve(
        array $configuredLanguageCodes,
        array $existingLanguageCodes,
        string $expectedLanguageCode
    ): void {
        $resolver = $this->createResolver($configuredLanguageCodes, $existingLanguageCodes);

        self::assertSame($expectedLanguageCode, $resolver->resolve()->languageCode);
    }

    /**
     * @return iterable<string, array{string[], string[], string}>
     */
    public static function provideForResolve(): iterable
    {
        yield 'first configured language exists' => [
            ['eng-GB', 'pol-PL'],
            ['eng-GB', 'pol-PL'],
            'eng-GB',
        ];

        yield 'first configured language is missing in the repository' => [
            ['eng-GB', 'pol-PL'],
            ['pol-PL'],
            'pol-PL',
        ];

        yield 'configuration order wins over repository order' => [
            ['ger-DE', 'pol-PL'],
            ['pol-PL', 'ger-DE'],
            'ger-DE',
        ];

        yield 'no configured languages falls back to the default language' => [
            [],
            ['eng-GB'],
            'eng-GB',
        ];
    }

    public function testResolveThrowsWhenNoConfiguredLanguageExists(): void
    {
        $resolver = $this->createResolver(['eng-GB', 'ger-DE'], ['pol-PL']);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Could not find 'Language' with identifier 'eng-GB, ger-DE'");

        $resolver->resolve();
    }

    /**
     * @dataProvider provideForGetLanguageCodes
     *
     * @param string[] $configuredLanguageCodes
     * @param string[] $expectedLanguageCodes
     */
    public function testGetLanguageCodes(
        array $configuredLanguageCodes,
        array $expectedLanguageCodes
    ): void {
        $resolver = $this->createResolver($configuredLanguageCodes, []);

        self::assertSame($expectedLanguageCodes, $resolver->getLanguageCodes());
    }

    /**
     * @return iterable<string, array{string[], string[]}>
     */
    public static function provideForGetLanguageCodes(): iterable
    {
        yield 'configured languages in configuration order' => [
            ['ger-DE', 'pol-PL'],
            ['ger-DE', 'pol-PL'],
        ];

        yield 'no configured languages falls back to the default language' => [
            [],
            ['eng-GB'],
        ];
    }

    /**
     * @param string[] $configuredLanguageCodes
     * @param string[] $existingLanguageCodes
     */
    private function createResolver(
        array $configuredLanguageCodes,
        array $existingLanguageCodes
    ): MainLanguageResolver {
        $configResolver = $this->createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturn($configuredLanguageCodes);

        $languageService = $this->createStub(LanguageService::class);
        $languageService->method('getDefaultLanguageCode')->willReturn('eng-GB');
        $languageService->method('loadLanguageListByCode')->willReturnCallback(
            static function (array $languageCodes) use ($existingLanguageCodes): array {
                $languages = [];
                foreach (array_intersect($existingLanguageCodes, $languageCodes) as $languageCode) {
                    $languages[$languageCode] = new Language(['languageCode' => $languageCode]);
                }

                return $languages;
            }
        );

        return new MainLanguageResolver($configResolver, $languageService);
    }
}
