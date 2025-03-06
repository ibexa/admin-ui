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
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class DraftListActionMenuBuilder extends AbstractActionBuilder implements TranslationContainerInterface
{
    public const ITEM_EDIT_DRAFT = 'draft_list__action__content_edit';

    protected function getConfigureEventName(): string
    {
        return self::class;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
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

        $contentEditDraftAction = $this->contentAwareActionItemFactory->createEditDraftAction(
            self::ITEM_EDIT_DRAFT,
            $versionInfo
        );

        $menu->addChild($contentEditDraftAction);

        return $menu;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(self::ITEM_EDIT_DRAFT, 'ibexa_action_menu')->setDesc('Edit'),
        ];
    }
}
