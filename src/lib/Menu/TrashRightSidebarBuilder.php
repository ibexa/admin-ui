<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Trash contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
final class TrashRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__EMPTY = 'trash__sidebar_right__empty_trash';

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        private readonly PermissionResolver $permissionResolver,
        private readonly TrashService $trashService
    ) {
        parent::__construct($factory, $eventDispatcher);
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::TRASH_SIDEBAR_RIGHT;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var bool $canDelete */
        $canDelete = $this->permissionResolver->hasAccess('content', 'cleantrash');
        $trashItemsCount = $this->trashService->findTrashItems(new Query())->totalCount;
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $trashEmptyAttributes = [
            'data-bs-target' => '#confirmEmptyTrash',
            'data-bs-toggle' => 'modal',
        ];

        $menu->addChild(
            $this->createMenuItem(self::ITEM__EMPTY, [
                'attributes' => $canDelete > 0 && $trashItemsCount > 0
                    ? $trashEmptyAttributes
                    : ['class' => 'disabled'],
            ])
        );

        return $menu;
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__EMPTY, 'ibexa_menu'))->setDesc('Empty Trash'),
        ];
    }
}
