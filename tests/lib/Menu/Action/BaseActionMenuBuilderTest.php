<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Menu\Action;

use Ibexa\AdminUi\Menu\MenuItemFactory;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as ApiVersionInfo;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\User\User;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseActionMenuBuilderTest extends TestCase
{
    protected const ROUTE_VERSION_HAS_NO_CONFLICT = 'ibexa.version.has_no_conflict';
    protected const ROUTE_CONTENT_EDIT_DRAFT = '/content/edit/draft/%d/%d/%s';
    protected const ROUTE_USER_UPDATE = '/user/update/%d/%d/%s';
    protected const EDIT_ACTION_ITEM_EXTRAS = [
        'icon' => 'edit',
        'orderNumber' => 200,
        'translation_domain' => 'ibexa_action_menu',
    ];
    protected const EDIT_ACTION_ITEM_ATTRIBUTES = [
        'class' => 'ibexa-btn--content-draft-edit',
        'data-content-id' => 1,
        'data-language-code' => 'eng-GB',
        'data-version-has-conflict-url' => '/version/has-no-conflict/1/1/eng-GB',
    ];

    protected MenuItemFactoryInterface $menuItemFactory;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject */
    protected EventDispatcherInterface $eventDispatcher;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService&\PHPUnit\Framework\MockObject\MockObject */
    protected ContentService $contentService;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject */
    protected UrlGeneratorInterface $urlGenerator;

    /** @var \Ibexa\Contracts\Core\Repository\UserService&\PHPUnit\Framework\MockObject\MockObject */
    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->menuItemFactory = new MenuItemFactory(
            new MenuFactory(),
            $this->createMock(PermissionResolver::class),
            $this->createMock(LocationService::class)
        );
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    /**
     * @param array<string, mixed> $extras
     * @param array<string, mixed> $attributes
     */
    protected function assertActionItemSame(
        ItemInterface $menu,
        ?string $url,
        string $itemName,
        array $extras,
        array $attributes
    ): void {
        $actionItem = $menu->getChild($itemName);

        self::assertNotNull($actionItem);

        self::assertSame($url, $actionItem->getUri());
        self::assertSame($itemName, $actionItem->getLabel());
        self::assertSame($extras, $actionItem->getExtras());
        self::assertSame($attributes, $actionItem->getAttributes());
    }

    protected function createVersionInfo(int $status = ApiVersionInfo::STATUS_DRAFT): ApiVersionInfo
    {
        return new VersionInfo(
            [
                'status' => $status,
                'versionNo' => 1,
                'initialLanguage' => new Language([
                    'languageCode' => 'eng-GB',
                ]),
                'contentInfo' => new ContentInfo(['id' => 1]),
                'creator' => $this->createMock(User::class),
            ]
        );
    }

    protected function mockUrlGeneratorGenerate(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->willReturnCallback(
                function (string $routeName, array $parameters): ?string {
                    if ($routeName === self::ROUTE_VERSION_HAS_NO_CONFLICT) {
                        return $this->getUrl('/version/has-no-conflict/%d/%d/%s', $parameters);
                    }

                    if ($routeName === 'ibexa.content.draft.edit') {
                        $pattern = self::ROUTE_CONTENT_EDIT_DRAFT;
                        if (!empty($parameters['locationId'])) {
                            $pattern .= '/%d';
                        }

                        return $this->getUrl($pattern, $parameters);
                    }

                    if ($routeName === 'ibexa.user.update') {
                        $pattern = self::ROUTE_USER_UPDATE;
                        if (!empty($parameters['locationId'])) {
                            $pattern .= '/%d';
                        }

                        return $this->getUrl($pattern, $parameters);
                    }

                    return null;
                }
            );
    }

    protected function mockContentServiceLoadContentByVersionInfo(
        ApiVersionInfo $versionInfo,
        Content $content
    ): void {
        $this->contentService
            ->method('loadContentByVersionInfo')
            ->with($versionInfo)
            ->willReturn($content);
    }

    protected function mockUserServiceIsUser(
        Content $content,
        bool $isUser = true
    ): void {
        $this->userService
            ->method('isUser')
            ->with($content)
            ->willReturn($isUser);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getUrl(
        string $urlPattern,
        array $parameters
    ): string {
        return vsprintf($urlPattern, $parameters);
    }
}
