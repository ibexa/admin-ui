<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu\Action;

use Ibexa\Contracts\AdminUi\Menu\AbstractActionBuilder;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Model\Message;
use Knp\Menu\ItemInterface;

final class VersionListActionMenuBuilder extends AbstractActionBuilder
{
    public const ITEM_EDIT_DRAFT = 'version_list__action__content_edit';
    public const ITEM_RESTORE_VERSION = 'version_list__action__restore_version';

    private const ICON_ARCHIVE_RESTORE = 'archive-restore';

    protected function getConfigureEventName(): string
    {
        return self::class;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    protected function createStructure(array $options): ItemInterface
    {
        $menu = $this->createActionItem('root_action_list');

        $versionInfo = $options['versionInfo'];
        if (!$versionInfo instanceof VersionInfo) {
            throw new InvalidArgumentException(
                '$versionInfo',
                'Version info expected to be type of ' . VersionInfo::class
            );
        }

        if ($versionInfo->isDraft()) {
            $isDraftConflict = $options['isDraftConflict'] ?? false;
            $locationId = $options['locationId'] ?? null;

            $contentEditDraftAction = $this->contentAwareActionItemFactory->createEditDraftAction(
                self::ITEM_EDIT_DRAFT,
                $versionInfo,
                $isDraftConflict,
                $locationId
            );

            $menu->addChild($contentEditDraftAction);
        }

        if ($versionInfo->isArchived()) {
            $restore = $this->createActionItem(
                self::ITEM_RESTORE_VERSION,
                [
                    'attributes' => [
                        'class' => 'ibexa-btn--content-edit',
                        'data-content-id' => $versionInfo->getContentInfo()->getId(),
                        'data-language-code' => $versionInfo->getInitialLanguage()->getLanguageCode(),
                        'data-version-no' => $versionInfo->getVersionNo(),
                    ],
                    'extras' => [
                        'icon' => self::ICON_ARCHIVE_RESTORE,
                        'orderNumber' => 100,
                    ],
                ]
            );

            $menu->addChild($restore);
        }

        return $menu;
    }

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            Message::create(self::ITEM_EDIT_DRAFT, 'ibexa_action_menu')->setDesc('Edit'),
            Message::create(self::ITEM_RESTORE_VERSION, 'ibexa_action_menu')->setDesc('Restore archived version'),
        ];
    }
}
