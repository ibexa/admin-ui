<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Permission;

use Ibexa\AdminUi\Permission\LimitationResolver;
use Ibexa\AdminUi\Permission\LimitationResolverInterface;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\User\LookupPolicyLimitations;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Ibexa\AdminUi\Permission\LimitationResolver::class)]
final class LimitationResolverTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver&\PHPUnit\Framework\MockObject\MockObject */
    private PermissionResolver $permissionResolver;

    private LimitationResolverInterface $limitationResolver;

    protected function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);

        $this->limitationResolver = new LimitationResolver(
            $this->createStub(ContentService::class),
            $this->createStub(ContentTypeService::class),
            $this->createStub(LanguageService::class),
            $this->createStub(LocationService::class),
            new LookupLimitationsTransformer(),
            $this->permissionResolver
        );
    }

    /**
     * @param array<array{
     *     languageCode: string,
     *     name: string,
     *     hasAccess: bool,
     * }> $expected
     * @param iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Language> $languages
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataForTestGetLanguageLimitations')]
    public function testGetLanguageLimitations(
        array $expected,
        ContentInfo $contentInfo,
        Location $location,
        LookupLimitationResult $lookupLimitationResult,
        iterable $languages
    ): void {
        $this->mockPermissionResolverLookupLimitations(
            $contentInfo,
            $location,
            $lookupLimitationResult
        );
        self::assertEquals(
            $expected,
            $this->limitationResolver->getLanguageLimitations(
                'edit',
                $contentInfo,
                $languages,
                [$location]
            )
        );
    }

    /**
     * @return iterable<array{
     *     array<array{
     *          languageCode: string,
     *          name: string,
     *          hasAccess: bool,
     *     }>,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Location,
     *     \Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult,
     *     iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Language>
     * }>
     */
    public static function provideDataForTestGetLanguageLimitations(): iterable
    {
        $english = self::createLanguage(1, true, 'eng-GB', 'English');
        $german = self::createLanguage(2, true, 'ger-DE', 'German');
        $french = self::createLanguage(3, false, 'fra-FR', 'French');
        $contentInfo = self::createContentInfo();
        $location = self::createLocation();
        $languages = [
            $english,
            $german,
            $french,
        ];

        yield 'No access to all languages' => [
            [
                self::getLanguageAccessData(false, $english),
                self::getLanguageAccessData(false, $german),
                self::getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(false),
            $languages,
        ];

        yield 'Access to all enabled languages' => [
            [
                self::getLanguageAccessData(true, $english),
                self::getLanguageAccessData(true, $german),
                self::getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(true),
            $languages,
        ];

        yield 'Limited access to English language by policy limitation' => [
            [
                self::getLanguageAccessData(true, $english),
                self::getLanguageAccessData(false, $german),
                self::getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [],
                [
                    new LookupPolicyLimitations(
                        self::createStub(Policy::class),
                        [
                            self::createLanguageLimitation(['eng-GB']),
                        ]
                    ),
                ]
            ),
            $languages,
        ];

        yield 'Limited access to German language by role limitation' => [
            [
                self::getLanguageAccessData(false, $english),
                self::getLanguageAccessData(true, $german),
                self::getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [
                    self::createLanguageLimitation(['ger-DE']),
                ],
            ),
            $languages,
        ];

        yield 'Limited access to English and German languages by role and policy limitations' => [
            [
                self::getLanguageAccessData(true, $english),
                self::getLanguageAccessData(true, $german),
                self::getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [
                    self::createLanguageLimitation(['eng-GB', 'fra-FR']),
                ],
                [
                    new LookupPolicyLimitations(
                        self::createStub(Policy::class),
                        [
                            self::createLanguageLimitation(['ger-DE', 'fra-FR']),
                        ]
                    ),
                ]
            ),
            $languages,
        ];
    }

    private static function createContentInfo(): ContentInfo
    {
        return self::createStub(ContentInfo::class);
    }

    private static function createLocation(): Location
    {
        return self::createStub(Location::class);
    }

    private static function createLanguage(
        int $id,
        bool $enabled,
        string $languageCode,
        string $name
    ): Language {
        return new Language(
            [
                'id' => $id,
                'enabled' => $enabled,
                'languageCode' => $languageCode,
                'name' => $name,
            ]
        );
    }

    /**
     * @return array{
     *     languageCode: string,
     *     name: string,
     *     hasAccess: bool,
     * }
     */
    private static function getLanguageAccessData(
        bool $hasAccess,
        Language $language
    ): array {
        return [
            'languageCode' => $language->getLanguageCode(),
            'name' => $language->getName(),
            'hasAccess' => $hasAccess,
        ];
    }

    /**
     * @param array<string> $limitationValues
     */
    private static function createLanguageLimitation(array $limitationValues): Limitation\LanguageLimitation
    {
        return new Limitation\LanguageLimitation(
            [
                'limitationValues' => $limitationValues,
            ]
        );
    }

    private function mockPermissionResolverLookupLimitations(
        ContentInfo $contentInfo,
        Location $location,
        LookupLimitationResult $lookupLimitationResult
    ): void {
        $languageCodes = [
            'eng-GB',
            'ger-DE',
        ];
        $targets = [
            $location,
            (new VersionBuilder())->translateToAnyLanguageOf($languageCodes)->build(),
        ];

        $this->permissionResolver
            ->method('lookupLimitations')
            ->with(
                'content',
                'edit',
                $contentInfo,
                $targets,
                [Limitation::LANGUAGE],
            )
        ->willReturn($lookupLimitationResult);
    }
}
