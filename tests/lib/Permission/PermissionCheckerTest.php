<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Permission;

use Ibexa\AdminUi\Permission\LimitationResolverInterface;
use Ibexa\AdminUi\Permission\PermissionChecker;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\Content as CoreContent;
use Ibexa\Core\Repository\Values\User\Policy;
use Ibexa\Core\Repository\Values\User\User as CoreUser;
use PHPUnit\Framework\TestCase;

class PermissionCheckerTest extends TestCase
{
    private const USER_ID = 14;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver&\PHPUnit\Framework\MockObject\MockObject */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\Core\Repository\UserService&\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \Ibexa\AdminUi\Permission\LimitationResolverInterface&\PHPUnit\Framework\MockObject\MockObject */
    private LimitationResolverInterface $permissionLimitationResolver;

    /** @var \Ibexa\AdminUi\Permission\PermissionChecker */
    private $permissionChecker;

    public function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->permissionResolver
            ->method('getCurrentUserReference')
            ->willReturn($this->generateUser(self::USER_ID));

        $this->permissionLimitationResolver = $this->createMock(LimitationResolverInterface::class);
        $this->userService = $this->createMock(UserService::class);

        $this->permissionChecker = new PermissionChecker(
            $this->permissionResolver,
            $this->permissionLimitationResolver,
            $this->userService,
        );
    }

    /**
     * @dataProvider restrictionsProvider
     */
    public function testGetRestrictions(array $hasAccess, string $class, array $expectedRestrictions): void
    {
        $actual = $this->permissionChecker->getRestrictions($hasAccess, $class);

        self::assertEquals($expectedRestrictions, $actual);
    }

    public function restrictionsProvider(): array
    {
        return [
            'noPoliciesAndNoRoleLimitation' => [
                [
                    [
                        'limitation' => null,
                        'policies' => [],
                    ],
                ],
                'anyClassName',
                [],
            ],
            'twoPoliciesWihOneLimitationAndRoleLimitationWithoutClassName' => [
                [
                    [
                        'limitation' => new Limitation\SectionLimitation(['limitationValues' => [44]]),
                        'policies' => [
                            new Policy(['limitations' => [new Limitation\ContentTypeLimitation(['limitationValues' => [2, 3]])]]),
                            new Policy(['limitations' => [new Limitation\LanguageLimitation(['limitationValues' => [2, 3]])]]),
                        ],
                    ],
                ],
                'anyClassName',
                [],
            ],
            'onePolicyOneLimitation' => [
                [
                    [
                        'limitation' => null,
                        'policies' => [
                            new Policy(['limitations' => [new Limitation\ContentTypeLimitation(['limitationValues' => [2, 3]])]]),
                        ],
                    ],
                ],
                Limitation\ContentTypeLimitation::class,
                [2, 3],
            ],
            'twoPoliciesWihOneLimitation' => [
                [
                    [
                        'limitation' => null,
                        'policies' => [
                            new Policy(['limitations' => [new Limitation\ContentTypeLimitation(['limitationValues' => [2, 3]])]]),
                            new Policy(['limitations' => [new Limitation\LanguageLimitation(['limitationValues' => [2, 3]])]]),
                        ],
                    ],
                ],
                Limitation\ContentTypeLimitation::class,
                [2, 3],
            ],
            'twoPoliciesWihOneLimitationAndRoleLimitation' => [
                [
                    [
                        'limitation' => new Limitation\SectionLimitation(['limitationValues' => [44]]),
                        'policies' => [
                            new Policy(['limitations' => [new Limitation\ContentTypeLimitation(['limitationValues' => [2, 3]])]]),
                            new Policy(['limitations' => [new Limitation\LanguageLimitation(['limitationValues' => [2, 3]])]]),
                        ],
                    ],
                ],
                Limitation\SectionLimitation::class,
                [44],
            ],
            'noPoliciesAndRoleLimitation' => [
                [
                    [
                        'limitation' => new Limitation\SectionLimitation(['limitationValues' => [44]]),
                        'policies' => [],
                    ],
                ],
                Limitation\SectionLimitation::class,
                [],
            ],
            'policyWithoutLimitationAndWithRoleLimitation' => [
                [
                    [
                        'limitation' => new Limitation\SectionLimitation(['limitationValues' => [44]]),
                        'policies' => [new Policy()],
                    ],
                ],
                Limitation\SectionLimitation::class,
                [44],
            ],
        ];
    }

    public function testGetRestrictionsIsNotCached(): void
    {
        $hasAccessA = [
            [
                'limitation' => new Limitation\SectionLimitation(['limitationValues' => [44]]),
                'policies' => [new Policy()],
            ],
        ];

        $hasAccessB = [
            [
                'limitation' => null,
                'policies' => [
                    new Policy(['limitations' => [new Limitation\ContentTypeLimitation(['limitationValues' => [2, 3]])]]),
                ],
            ],
        ];

        self::assertEquals(
            [44],
            $this->permissionChecker->getRestrictions($hasAccessA, Limitation\SectionLimitation::class)
        );

        self::assertEquals(
            [2, 3],
            $this->permissionChecker->getRestrictions($hasAccessB, Limitation\ContentTypeLimitation::class)
        );
    }

    /**
     * @param int $id
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User
     */
    private function generateUser(int $id): User
    {
        $contentInfo = new Content\ContentInfo(['id' => $id]);
        $versionInfo = new CoreContent\VersionInfo(['contentInfo' => $contentInfo]);
        $content = new CoreContent\Content(['versionInfo' => $versionInfo]);

        return new CoreUser(['content' => $content]);
    }
}
