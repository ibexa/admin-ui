<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class FooterMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM__GETTING_STARTED = 'footer__getting_started';
    public const ITEM_DOCUMENTATION = 'footer__documentation';
    public const ITEM_BLOG = 'footer__blog';
    public const ITEM_IBEXA = 'footer__ibexa';

    /**
     * @param array<string, mixed> $options
     */
    protected function createStructure(array $options): ItemInterface
    {
        $menu = $this->createMenuItem('root');

        $this->addExternalLinkMenuItem(
            $menu,
            self::ITEM__GETTING_STARTED,
            'https://doc.ibexa.co/projects/userguide/en/latest/getting_started/get_started/'
        );

        $this->addExternalLinkMenuItem(
            $menu,
            self::ITEM_DOCUMENTATION,
            'https://doc.ibexa.co/projects/userguide/en/latest/'
        );

        $this->addExternalLinkMenuItem(
            $menu,
            self::ITEM_BLOG,
            'https://www.ibexa.co/blog'
        );

        $this->addExternalLinkMenuItem(
            $menu,
            self::ITEM_IBEXA,
            'https://www.ibexa.co/products'
        );

        return $menu;
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::FOOTER_MENU;
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__GETTING_STARTED, 'ibexa_menu'))->setDesc('Getting started'),
            (new Message(self::ITEM_DOCUMENTATION, 'ibexa_menu'))->setDesc('Help'),
            (new Message(self::ITEM_BLOG, 'ibexa_menu'))->setDesc('Blog'),
            (new Message(self::ITEM_IBEXA, 'ibexa_menu'))->setDesc('Ibexa'),
        ];
    }

    private function addExternalLinkMenuItem(ItemInterface $menu, string $id, string $uri): void
    {
        $item = $this->createMenuItem($id, [
            'uri' => $uri,
            'linkAttributes' => [
                'target' => '_blank',
            ],
        ]);

        $menu->addChild($item);
    }
}
