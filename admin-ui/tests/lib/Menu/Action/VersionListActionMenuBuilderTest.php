<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Menu\Action;

use Ibexa\AdminUi\Menu\Action\VersionListActionMenuBuilder;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as ApiVersionInfo;

/**
 * @covers \Ibexa\AdminUi\Menu\Action\VersionListActionMenuBuilder
 */
final class VersionListActionMenuBuilderTest extends BaseActionMenuBuilderTest
{
    private const ITEM_EDIT_DRAFT = 'version_list__action__content_edit';
    private const ITEM_RESTORE_VERSION = 'version_list__action__restore_version';

    private const RESTORE_ACTION_ITEM_EXTRAS = [
        'icon' => 'archive-restore',
        'orderNumber' => 10,
        'translation_domain' => 'ibexa_action_menu',
    ];

    private VersionListActionMenuBuilder $versionListActionMenuBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->versionListActionMenuBuilder = new VersionListActionMenuBuilder(
            $this->menuItemFactory,
            $this->eventDispatcher,
            $this->contentService,
            $this->translator,
            $this->urlGenerator,
            $this->userService
        );
    }

    public function testThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Argument \'$versionInfo\' is invalid: Version info expected to be of type "%s" but got "string"',
                ApiVersionInfo::class
            )
        );

        $this->versionListActionMenuBuilder->build(['versionInfo' => 'foo']);
    }

    /**
     * @dataProvider provideDataForTestBuildVersionListActionMenu
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $extras
     * @param array<string, mixed> $attributes
     */
    public function testBuildVersionListActionMenu(
        array $options,
        string $itemName,
        ?string $url,
        array $extras,
        array $attributes
    ): void {
        $this->mockUrlGeneratorGenerate();
        $this->mockTranslatorTranslate();

        $menu = $this->versionListActionMenuBuilder->build($options);

        $this->assertActionItemSame(
            $menu,
            $url,
            $itemName,
            $extras,
            $attributes
        );
    }

    /**
     * @return iterable<string, array{
     *     array<string, mixed>,
     *     string,
     *     ?string,
     *     array<string, mixed>,
     *     array<string, mixed>,
     * }>
     */
    public function provideDataForTestBuildVersionListActionMenu(): iterable
    {
        $versionInfo = $this->createVersionInfo();

        yield 'Edit draft action item' => [
            ['versionInfo' => $versionInfo],
            self::ITEM_EDIT_DRAFT,
            null,
            self::EDIT_ACTION_ITEM_EXTRAS,
            array_merge(
                self::EDIT_ACTION_ITEM_ATTRIBUTES,
                ['data-content-draft-edit-url' => '/content/edit/draft/1/1/eng-GB']
            ),
        ];

        yield 'Edit draft action item - with location' => [
            [
                'versionInfo' => $versionInfo,
                'locationId' => 5,
            ],
            self::ITEM_EDIT_DRAFT,
            null,
            self::EDIT_ACTION_ITEM_EXTRAS,
            array_merge(
                self::EDIT_ACTION_ITEM_ATTRIBUTES,
                ['data-content-draft-edit-url' => '/content/edit/draft/1/1/eng-GB/5']
            ),
        ];

        yield 'Edit draft action item - draft conflict' => [
            [
                'versionInfo' => $versionInfo,
                'isDraftConflict' => true,
            ],
            self::ITEM_EDIT_DRAFT,
            '/content/edit/draft/1/1/eng-GB',
            self::EDIT_ACTION_ITEM_EXTRAS,
            ['class' => self::IBEXA_BTN_CONTENT_DRAFT_EDIT_CLASS],
        ];

        yield 'Restore version action item' => [
            ['versionInfo' => $this->createVersionInfo(ApiVersionInfo::STATUS_ARCHIVED)],
            self::ITEM_RESTORE_VERSION,
            null,
            self::RESTORE_ACTION_ITEM_EXTRAS,
            [
                'class' => 'ibexa-btn--content-edit',
                'data-content-id' => 1,
                'data-language-code' => 'eng-GB',
                'data-version-no' => 1,
            ],
        ];
    }

    public function testAddUserUpdateItemAction(): void
    {
        $versionInfo = $this->createVersionInfo();
        $user = $this->createMock(Content::class);

        $this->mockUrlGeneratorGenerate();
        $this->mockContentServiceLoadContentByVersionInfo($versionInfo, $user);
        $this->mockUserServiceIsUser($user);
        $this->mockTranslatorTranslate();

        $menu = $this->versionListActionMenuBuilder->build(['versionInfo' => $versionInfo]);

        $this->assertActionItemSame(
            $menu,
            null,
            self::ITEM_EDIT_DRAFT,
            self::EDIT_ACTION_ITEM_EXTRAS,
            array_merge(
                self::EDIT_ACTION_ITEM_ATTRIBUTES,
                ['data-content-draft-edit-url' => '/user/update/1/1/eng-GB']
            )
        );
    }
}
