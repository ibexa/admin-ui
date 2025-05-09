<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserPasswordChangeRightSidebarListener implements EventSubscriberInterface, TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__UPDATE = 'user_password_change__sidebar_right__update';
    public const ITEM__CANCEL = 'user_password_change__sidebar_right__cancel';

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [ConfigureMenuEvent::USER_PASSWORD_CHANGE_SIDEBAR_RIGHT => 'onUserPasswordChangeRightSidebarConfigure'];
    }

    /**
     * @param \Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent $event
     */
    public function onUserPasswordChangeRightSidebarConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();

        $menu->addChild(
            self::ITEM__UPDATE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => '#user_password_change_change',
                ],
                'extras' => ['translation_domain' => 'ibexa_menu'],
            ]
        );
        $menu->addChild(
            self::ITEM__CANCEL,
            [
                'extras' => ['translation_domain' => 'ibexa_menu'],
                'route' => 'ibexa.user_settings.list',
                'routeParameters' => [
                    '_fragment' => 'ibexa-tab-my-account-settings',
                ],
            ]
        );
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__UPDATE, 'ibexa_menu'))->setDesc('Save and close'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard'),
        ];
    }
}
