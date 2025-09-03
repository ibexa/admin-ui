<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Admin\Role;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Section Copy contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
final class RoleCopyRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__SAVE = 'role_copy__sidebar_right__save';
    public const string ITEM__CANCEL = 'role_copy__sidebar_right__cancel';

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct($factory, $eventDispatcher);
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::ROLE_COPY_SIDEBAR_RIGHT;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $menu->setChildren([
            self::ITEM__SAVE => $this->createMenuItem(
                self::ITEM__SAVE,
                [
                    'attributes' => [
                        'class' => 'ibexa-btn--trigger',
                        'data-click' => '#role_copy_copy',
                        'data-extra-classes' => 'ibexa-tooltip--medium',
                        'data-placement' => 'left',
                        'title' => $this->translator->trans(
                            /** @Ignore */
                            self::ITEM__SAVE,
                            [],
                            'ibexa_menu'
                        ),
                    ],
                ]
            ),
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'attributes' => [
                        'data-extra-classes' => 'ibexa-tooltip--medium',
                        'data-placement' => 'left',
                        'title' => $this->translator->trans(
                            /** @Ignore */
                            self::ITEM__CANCEL,
                            [],
                            'ibexa_menu'
                        ),
                    ],
                    'route' => 'ibexa.role.list',
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
            (new Message(self::ITEM__SAVE, 'ibexa_menu'))->setDesc('Copy'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard changes'),
        ];
    }
}
