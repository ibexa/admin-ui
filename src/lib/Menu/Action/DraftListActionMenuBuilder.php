<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Action;

use Ibexa\Contracts\AdminUi\Menu\AbstractActionBuilder;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Annotation\Ignore;
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
     */
    protected function createStructure(array $options): ItemInterface
    {
        $versionInfo = $options['versionInfo'];
        if (!$versionInfo instanceof VersionInfo) {
            throw new InvalidArgumentException(
                '$versionInfo',
                sprintf(
                    'Version info expected to be of type "%s" but got "%s"',
                    VersionInfo::class,
                    get_debug_type($versionInfo)
                )
            );
        }

        $menu = $this->createActionItem('root_action_list');

        $parameters['label'] = $this->translator->trans(
            /** @Ignore */
            self::ITEM_EDIT_DRAFT,
            [],
            self::TRANSLATION_DOMAIN
        );
        $parameters['attributes']['class'] = self::IBEXA_BTN_CONTENT_DRAFT_EDIT_CLASS;

        $contentEditDraftAction = $this->createEditDraftButtonAction(
            $versionInfo,
            self::ITEM_EDIT_DRAFT,
            $parameters
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
