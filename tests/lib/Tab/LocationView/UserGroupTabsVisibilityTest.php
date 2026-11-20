<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\PoliciesTab;
use Ibexa\AdminUi\Tab\LocationView\RolesTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[CoversClass(RolesTab::class)]
#[CoversClass(PoliciesTab::class)]
final class UserGroupTabsVisibilityTest extends TestCase
{
    /**
     * @param class-string<RolesTab|PoliciesTab> $tabClass
     */
    #[DataProvider('provideTabsAndUserGroupDecision')]
    public function testTabIsVisibleForContentUserServiceRecognisesAsUserGroup(
        string $tabClass,
        bool $isUserGroup
    ): void {
        $content = self::createStub(Content::class);

        $permissionResolver = self::createStub(PermissionResolver::class);
        $permissionResolver->method('canUser')->willReturn(true);

        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturn(['user']);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())->method('isUserGroup')->with($content)->willReturn($isUserGroup);

        $tab = new $tabClass(
            self::createStub(Environment::class),
            self::createStub(TranslatorInterface::class),
            self::createStub(DatasetFactory::class),
            $permissionResolver,
            self::createStub(EventDispatcherInterface::class),
            $configResolver,
            $userService
        );

        self::assertSame($isUserGroup, $tab->evaluate([
            'content' => $content,
            'contentType' => new ContentType(['identifier' => 'custom_user_group']),
        ]));
    }

    /**
     * @return iterable<string, array{class-string<RolesTab|PoliciesTab>, bool}>
     */
    public static function provideTabsAndUserGroupDecision(): iterable
    {
        yield 'roles, user group' => [RolesTab::class, true];
        yield 'roles, not a user group' => [RolesTab::class, false];
        yield 'policies, user group' => [PoliciesTab::class, true];
        yield 'policies, not a user group' => [PoliciesTab::class, false];
    }
}
