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
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI content type View contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class ContentTypeRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__EDIT = 'content_type__sidebar_right__edit';

    private PermissionResolver $permissionResolver;

    private TranslatorInterface $translator;

    /**
     * @param \Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface $factory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        PermissionResolver $permissionResolver,
        TranslatorInterface $translator
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->permissionResolver = $permissionResolver;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_TYPE_SIDEBAR_RIGHT;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $options['content_type'];

        /** @var \Knp\Menu\ItemInterface $menu */
        $menu = $this->factory->createItem('root');

        $editAttributes = [
            'class' => 'ibexa-btn--extra-actions ibexa-btn--edit',
            'data-actions' => 'edit',
        ];
        $canEdit = $this->permissionResolver->canUser(
            'class',
            'update',
            $contentType
        ) && $this->permissionResolver->hasAccess('class', 'create');

        $menu->addChild(
            $this->createMenuItem(
                self::ITEM__EDIT,
                [
                    'attributes' => $canEdit
                        ? $editAttributes
                        : array_merge($editAttributes, ['disabled' => 'disabled']),
                ]
            )
        );

        return $menu;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__EDIT, 'ibexa_menu'))->setDesc('Edit'),
        ];
    }
}
