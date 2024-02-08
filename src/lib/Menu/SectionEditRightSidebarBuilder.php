<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Section Edit contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class SectionEditRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__SAVE = 'section_edit__sidebar_right__save';
    public const ITEM__SAVE_AND_CLOSE = 'section_edit__sidebar_right__save_and_close';
    public const ITEM__CANCEL = 'section_edit__sidebar_right__cancel';

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->translator = $translator;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::SECTION_EDIT_SIDEBAR_RIGHT;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \InvalidArgumentException
     * @throws ApiExceptions\BadStateException
     * @throws \InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section $section */
        $section = $options['section'];

        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $saveAndCloseItem = $this->createMenuItem(
            self::ITEM__SAVE_AND_CLOSE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => sprintf('#update-section-%d_update', $section->id),
                ],
            ]
        );

        $saveAndCloseItem->addChild(
            self::ITEM__SAVE,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => sprintf('#update-section-%d_update_and_edit', $section->id),
                ],
            ]
        );

        $menu->setChildren([
            self::ITEM__SAVE_AND_CLOSE => $saveAndCloseItem,
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'route' => 'ibexa.section.list',
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
            (new Message(self::ITEM__SAVE_AND_CLOSE, 'ibexa_menu'))->setDesc('Save and close'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Discard changes'),
        ];
    }
}

class_alias(SectionEditRightSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\SectionEditRightSidebarBuilder');
