<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\UserSetting;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class UserSettingUpdateRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__SAVE = 'user_setting_update__sidebar_right__save';
    public const string ITEM__CANCEL = 'user_setting_update__sidebar_right__cancel';

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::USER_SETTING_UPDATE_SIDEBAR_RIGHT;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $saveItem = $this->createMenuItem(
            self::ITEM__SAVE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => '#user_setting_update_update',
                ],
            ]
        );

        $menu->setChildren([
            self::ITEM__SAVE => $saveItem,
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'route' => 'ibexa.user_settings.list',
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
            (new Message(self::ITEM__SAVE, 'ibexa_menu'))->setDesc('Save'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard'),
        ];
    }
}
