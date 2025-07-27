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

/**
 * @covers \Ibexa\AdminUi\Permission\LimitationResolver
 */
final class LimitationResolverTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver&\PHPUnit\Framework\MockObject\MockObject */
    private PermissionResolver $permissionResolver;

    private LimitationResolverInterface $limitationResolver;

    protected function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);

        $this->limitationResolver = new LimitationResolver(
            $this->createMock(ContentService::class),
            $this->createMock(ContentTypeService::class),
            $this->createMock(LanguageService::class),
            $this->createMock(LocationService::class),
            new LookupLimitationsTransformer(),
            $this->permissionResolver
        );
    }

    /**
     * @dataProvider provideDataForTestGetLanguageLimitations
     *
     * @param array<array{
     *     languageCode: string,
     *     name: string,
     *     hasAccess: bool,
     * }> $expected
     * @param iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Language> $languages
     */
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
    public function provideDataForTestGetLanguageLimitations(): iterable
    {
        $english = $this->createLanguage(1, true, 'eng-GB', 'English');
        $german = $this->createLanguage(2, true, 'ger-DE', 'German');
        $french = $this->createLanguage(3, false, 'fra-FR', 'French');
        $contentInfo = $this->createContentInfo();
        $location = $this->createLocation();
        $languages = [
            $english,
            $german,
            $french,
        ];

        yield 'No access to all languages' => [
            [
                $this->getLanguageAccessData(false, $english),
                $this->getLanguageAccessData(false, $german),
                $this->getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(false),
            $languages,
        ];

        yield 'Access to all enabled languages' => [
            [
                $this->getLanguageAccessData(true, $english),
                $this->getLanguageAccessData(true, $german),
                $this->getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(true),
            $languages,
        ];

        yield 'Limited access to English language by policy limitation' => [
            [
                $this->getLanguageAccessData(true, $english),
                $this->getLanguageAccessData(false, $german),
                $this->getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [],
                [
                    new LookupPolicyLimitations(
                        $this->createMock(Policy::class),
                        [
                            $this->createLanguageLimitation(['eng-GB']),
                        ]
                    ),
                ]
            ),
            $languages,
        ];

        yield 'Limited access to German language by role limitation' => [
            [
                $this->getLanguageAccessData(false, $english),
                $this->getLanguageAccessData(true, $german),
                $this->getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [
                    $this->createLanguageLimitation(['ger-DE']),
                ],
            ),
            $languages,
        ];

        yield 'Limited access to English and German languages by role and policy limitations' => [
            [
                $this->getLanguageAccessData(true, $english),
                $this->getLanguageAccessData(true, $german),
                $this->getLanguageAccessData(false, $french),
            ],
            $contentInfo,
            $location,
            new LookupLimitationResult(
                true,
                [
                    $this->createLanguageLimitation(['eng-GB', 'fra-FR']),
                ],
                [
                    new LookupPolicyLimitations(
                        $this->createMock(Policy::class),
                        [
                            $this->createLanguageLimitation(['ger-DE', 'fra-FR']),
                        ]
                    ),
                ]
            ),
            $languages,
        ];
    }

    private function createContentInfo(): ContentInfo
    {
        return $this->createMock(ContentInfo::class);
    }

    private function createLocation(): Location
    {
        return $this->createMock(Location::class);
    }

    private function createLanguage(
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
    private function getLanguageAccessData(
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
    private function createLanguageLimitation(array $limitationValues): Limitation\LanguageLimitation
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
