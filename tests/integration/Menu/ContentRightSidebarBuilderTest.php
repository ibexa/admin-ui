<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\Menu;

use Ibexa\AdminUi\Menu\ContentRightSidebarBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;
use Ibexa\Core\MVC\Symfony\Event\ScopeChangeEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Ibexa\AdminUi\Menu\ContentRightSidebarBuilder
 */
final class ContentRightSidebarBuilderTest extends IbexaKernelTestCase
{
    private const FOLDER_CONTENT_TYPE_ID = 1;
    private const ARTICLE_CONTENT_TYPE_ID = 16;
    private const HOME_LOCATION_ID = 2;

    private ContentRightSidebarBuilder $builder;

    protected function setUp(): void
    {
        self::bootKernel();
        self::setAdministratorUser();

        self::getServiceByClassName(EventDispatcherInterface::class)->dispatch(
            new ScopeChangeEvent(new SiteAccess('admin')),
            MVCEvents::CONFIG_SCOPE_CHANGE
        );

        $this->builder = self::getServiceByClassName(ContentRightSidebarBuilder::class);
    }

    /**
     * @dataProvider provideContentCreateLimitations
     *
     * @param array<Limitation> $contentCreateLimitations
     */
    public function testMoveAndCopyRespectContentTypeOfCreatePolicy(
        array $contentCreateLimitations,
        bool $expectedEnabled
    ): void {
        $folder = $this->createFolder();
        self::getPermissionResolver()->setCurrentUserReference(
            $this->createUserWithContentCreateLimitations($contentCreateLimitations)
        );

        $menu = $this->builder->createStructure([
            'location' => $folder->contentInfo->getMainLocation(),
            'content_type' => $folder->getContentType(),
            'content' => $folder,
        ]);

        foreach ([
            ContentRightSidebarBuilder::ITEM__MOVE,
            ContentRightSidebarBuilder::ITEM__COPY,
            ContentRightSidebarBuilder::ITEM__COPY_SUBTREE,
        ] as $itemName) {
            self::assertSame(
                $expectedEnabled,
                $this->isEnabled($menu, $itemName),
                sprintf('Unexpected state of "%s" menu item', $itemName)
            );
        }
    }

    /**
     * @return iterable<string, array{array<Limitation>, bool}>
     */
    public static function provideContentCreateLimitations(): iterable
    {
        yield 'no content type limitation' => [[], true];

        yield 'content type limitation including content type' => [
            [new ContentTypeLimitation(['limitationValues' => [self::FOLDER_CONTENT_TYPE_ID]])],
            true,
        ];

        yield 'content type limitation including content type among others' => [
            [
                new ContentTypeLimitation([
                    'limitationValues' => [self::ARTICLE_CONTENT_TYPE_ID, self::FOLDER_CONTENT_TYPE_ID],
                ]),
            ],
            true,
        ];

        yield 'content type limitation excluding content type' => [
            [new ContentTypeLimitation(['limitationValues' => [self::ARTICLE_CONTENT_TYPE_ID]])],
            false,
        ];
    }

    public function testMoveAndCopyAreDisabledWithoutCreatePolicy(): void
    {
        $folder = $this->createFolder();
        self::getPermissionResolver()->setCurrentUserReference(
            $this->createUserWithPolicies(['user/login', 'content/read', 'content/versionread', 'content/manage_locations'])
        );

        $menu = $this->builder->createStructure([
            'location' => $folder->contentInfo->getMainLocation(),
            'content_type' => $folder->getContentType(),
            'content' => $folder,
        ]);

        self::assertFalse($this->isEnabled($menu, ContentRightSidebarBuilder::ITEM__MOVE));
        self::assertFalse($this->isEnabled($menu, ContentRightSidebarBuilder::ITEM__COPY));
        self::assertFalse($this->isEnabled($menu, ContentRightSidebarBuilder::ITEM__COPY_SUBTREE));
    }

    private function isEnabled(
        ItemInterface $menu,
        string $itemName
    ): bool {
        $item = $menu->getChild($itemName);
        self::assertNotNull($item, sprintf('Menu item "%s" is missing', $itemName));

        return $item->getAttribute('disabled') === null;
    }

    private function createFolder(): Content
    {
        $contentService = self::getContentService();
        $contentType = self::getContentTypeService()->loadContentTypeByIdentifier('folder');

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->setField('name', 'Folder');

        $draft = $contentService->createContent(
            $createStruct,
            [self::getLocationService()->newLocationCreateStruct(self::HOME_LOCATION_ID)]
        );

        return $contentService->publishVersion($draft->getVersionInfo());
    }

    /**
     * @param array<Limitation> $contentCreateLimitations
     */
    private function createUserWithContentCreateLimitations(array $contentCreateLimitations): User
    {
        return $this->createUserWithPolicies(
            ['user/login', 'content/read', 'content/versionread', 'content/manage_locations'],
            ['content/create' => $contentCreateLimitations]
        );
    }

    /**
     * @param string[] $unlimitedPolicies
     * @param array<string, array<Limitation>> $limitedPolicies
     */
    private function createUserWithPolicies(
        array $unlimitedPolicies,
        array $limitedPolicies = []
    ): User {
        $roleService = self::getRoleService();
        $userService = self::getUserService();

        $roleCreateStruct = $roleService->newRoleCreateStruct(uniqid('role_', true));
        $policies = array_merge(array_fill_keys($unlimitedPolicies, []), $limitedPolicies);
        foreach ($policies as $moduleFunction => $limitations) {
            [$module, $function] = explode('/', $moduleFunction);
            $policyCreateStruct = $roleService->newPolicyCreateStruct($module, $function);
            foreach ($limitations as $limitation) {
                $policyCreateStruct->addLimitation($limitation);
            }
            $roleCreateStruct->addPolicy($policyCreateStruct);
        }
        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);

        $login = uniqid('editor_', false);
        $userCreateStruct = $userService->newUserCreateStruct($login, "$login@test.local", 'Passw0rd!', 'eng-GB');
        $userCreateStruct->setField('first_name', $login);
        $userCreateStruct->setField('last_name', $login);
        $user = $userService->createUser($userCreateStruct, [$userService->loadUserGroup(4)]);

        $roleService->assignRoleToUser($roleService->loadRole($roleDraft->id), $user);

        return $user;
    }
}
