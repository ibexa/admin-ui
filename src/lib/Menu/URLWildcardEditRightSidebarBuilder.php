<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Tab\URLManagement\URLWildcardsTab;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class URLWildcardEditRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__SAVE_AND_CLOSE = 'url_wildcard_edit__sidebar_right__save_and_close';
    public const string ITEM__CANCEL = 'url_wildcard_edit__sidebar_right__cancel';

    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__SAVE_AND_CLOSE, 'ibexa_menu'))->setDesc('Save'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard changes'),
        ];
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::URL_WILDCARD_EDIT_SIDEBAR_RIGHT;
    }

    protected function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface $menu */
        $menu = $this->factory->createItem('root');

        $saveAndCloseItem = $this->createMenuItem(
            self::ITEM__SAVE_AND_CLOSE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => $options['submit_selector'],
                ],
            ]
        );

        $menu->setChildren([
            self::ITEM__SAVE_AND_CLOSE => $saveAndCloseItem,
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'route' => 'ibexa.url_management',
                    'routeParameters' => [
                        '_fragment' => URLWildcardsTab::URI_FRAGMENT,
                    ],
                ]
            ),
        ]);

        return $menu;
    }
}
