<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\UserSetting;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RouteExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for User Setting Edit contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class UserSettingUpdateRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /* Menu items */
    public const ITEM__SAVE = 'user_setting_update__sidebar_right__save';
    public const ITEM__SAVE_AND_EDIT = 'user_setting_update__sidebar_right__save_end_edit';
    public const ITEM__CANCEL = 'user_setting_update__sidebar_right__cancel';

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::USER_SETTING_UPDATE_SIDEBAR_RIGHT;
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
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');
        $route = $options['route'] ?? 'ibexa.user_settings.list';
        $routeParameters = $options['route_parameters'] ?? [];
        if (!$this->routeExists($route, $routeParameters)) {
            $route = 'ibexa.user_settings.list';
            $routeParameters = [];
        }

        $saveItem = $this->createMenuItem(
            self::ITEM__SAVE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => '#user_setting_update_update',
                ],
            ],
        );

        $saveItem->addChild(
            self::ITEM__SAVE_AND_EDIT,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => '#user_setting_update_update_and_edit',
                ],
                'extras' => [
                    'orderNumber' => 10,
                ],
            ]
        );

        $menu->setChildren([
            self::ITEM__SAVE => $saveItem,
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'route' => $route,
                    'routeParameters' => $routeParameters,
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
            (new Message(self::ITEM__SAVE, 'ibexa_menu'))->setDesc('Save and close'),
            (new Message(self::ITEM__SAVE_AND_EDIT, 'ibexa_menu'))->setDesc('Save'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard'),
        ];
    }

    /**
     * @param array<mixed> $routeParameters
     */
    private function routeExists(string $route, array $routeParameters): bool
    {
        try {
            $this->urlGenerator->generate($route, $routeParameters);

            return true;
        } catch (RouteExceptionInterface $e) {
            $this->logger->warning(
                sprintf('Invalid route in query. %s.', $e->getMessage()),
                [
                    'exception' => $e,
                ],
            );
        }

        return false;
    }
}

class_alias(UserSettingUpdateRightSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\UserSetting\UserSettingUpdateRightSidebarBuilder');
