<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Menu\Action;

use Ibexa\AdminUi\Menu\Action\DraftListActionMenuBuilder;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as ApiVersionInfo;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \Ibexa\AdminUi\Menu\Action\DraftListActionMenuBuilder
 */
final class DraftListActionMenuBuilderTest extends BaseActionMenuBuilderTest
{
    private DraftListActionMenuBuilder $actionMenuBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionMenuBuilder = new DraftListActionMenuBuilder(
            $this->menuItemFactory,
            $this->eventDispatcher,
            $this->contentService,
            $this->createMock(TranslatorInterface::class),
            $this->urlGenerator,
            $this->userService
        );
    }

    private const DRAFT_LIST_ACTION_CONTENT_EDIT = 'draft_list__action__content_edit';

    public function testThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Argument \'$versionInfo\' is invalid: Version info expected to be type of "%s" but got "string"',
                ApiVersionInfo::class
            )
        );

        $this->actionMenuBuilder->build(['versionInfo' => 'foo']);
    }

    public function testBuildDraftListActionMenu(): void
    {
        $this->mockUrlGeneratorGenerate();

        $menu = $this->actionMenuBuilder->build(['versionInfo' => $this->createVersionInfo()]);

        $this->assertActionItemSame(
            $menu,
            null,
            self::DRAFT_LIST_ACTION_CONTENT_EDIT,
            self::EDIT_ACTION_ITEM_EXTRAS,
            array_merge(
                self::EDIT_ACTION_ITEM_ATTRIBUTES,
                ['data-content-draft-edit-url' => '/content/edit/draft/1/1/eng-GB']
            )
        );
    }

    public function testAddUserUpdateItemAction(): void
    {
        $versionInfo = $this->createVersionInfo();
        $user = $this->createMock(Content::class);

        $this->mockUrlGeneratorGenerate();
        $this->mockContentServiceLoadContentByVersionInfo($versionInfo, $user);
        $this->mockUserServiceIsUser($user);

        $menu = $this->actionMenuBuilder->build(['versionInfo' => $versionInfo]);

        $this->assertActionItemSame(
            $menu,
            null,
            self::DRAFT_LIST_ACTION_CONTENT_EDIT,
            self::EDIT_ACTION_ITEM_EXTRAS,
            array_merge(
                self::EDIT_ACTION_ITEM_ATTRIBUTES,
                ['data-content-draft-edit-url' => '/user/update/1/1/eng-GB']
            )
        );
    }
}
