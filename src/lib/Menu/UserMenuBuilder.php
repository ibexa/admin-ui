<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI top menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class UserMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM_LOGOUT = 'user__content';
    public const ITEM_VIEW_PROFILE = 'user___view_profile';
    public const ITEM_USER_SETTINGS = 'user__settings';
    public const ITEM_BOOKMARK = 'user__bookmark';
    public const ITEM_NOTIFICATION = 'menu.notification';

    private TokenStorageInterface $tokenStorage;

    private UserProfileConfigurationInterface $userProfileConfiguration;

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        UserProfileConfigurationInterface $userProfileConfiguration
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->tokenStorage = $tokenStorage;
        $this->userProfileConfiguration = $userProfileConfiguration;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::USER_MENU;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $token = $this->tokenStorage->getToken();
        if (null !== $token && is_object($token->getUser())) {
            /** @var \Ibexa\Core\MVC\Symfony\Security\User $user */
            $user = $token->getUser();

            if ((new IsProfileAvailable($this->userProfileConfiguration))->isSatisfiedBy($user->getAPIUser())) {
                $menu->addChild(
                    $this->createMenuItem(
                        self::ITEM_VIEW_PROFILE,
                        [
                            'route' => 'ibexa.user.profile.view',
                            'routeParameters' => [
                                'userId' => $user->getAPIUser()->getUserId(),
                            ],
                            'extras' => [
                                'orderNumber' => 40,
                            ],
                        ]
                    )
                );
            }

            $menu->addChild(
                $this->createMenuItem(self::ITEM_USER_SETTINGS, [
                    'route' => 'ibexa.user_settings.list',
                    'extras' => [
                        'orderNumber' => 50,
                    ],
                ])
            );

            $menu->addChild(
                $this->createMenuItem(self::ITEM_LOGOUT, [
                    'route' => 'logout',
                    'attributes' => [
                        'class' => 'ibexa-popup-menu__item--with-border',
                    ],
                    'extras' => [
                        'orderNumber' => 60,
                    ],
                ])
            );
        }

        return $menu;
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM_LOGOUT, 'ibexa_menu'))->setDesc('Logout'),
            (new Message(self::ITEM_VIEW_PROFILE, 'ibexa_menu'))->setDesc('View Profile'),
            (new Message(self::ITEM_USER_SETTINGS, 'ibexa_menu'))->setDesc('User settings'),
            (new Message(self::ITEM_NOTIFICATION, 'ibexa_notifications'))->setDesc('View Notifications'),
        ];
    }
}
