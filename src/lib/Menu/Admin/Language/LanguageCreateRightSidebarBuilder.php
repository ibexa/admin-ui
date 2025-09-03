<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Admin\Language;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class LanguageCreateRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__CREATE = 'language_create__sidebar_right__create';
    public const string ITEM__CANCEL = 'language_create__sidebar_right__cancel';

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::LANGUAGE_CREATE_SIDEBAR_RIGHT;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        $saveAndCloseId = $options['save_and_close_id'];

        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $saveAndCloseItem = $this->createMenuItem(
            self::ITEM__CREATE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => sprintf('#%s', $saveAndCloseId),
                ],
            ]
        );

        $menu->setChildren([
            self::ITEM__CREATE => $saveAndCloseItem,
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'route' => 'ibexa.language.list',
                ]
            ),
        ]);

        return $menu;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__CREATE, 'ibexa_menu'))->setDesc('Save'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard'),
        ];
    }
}
