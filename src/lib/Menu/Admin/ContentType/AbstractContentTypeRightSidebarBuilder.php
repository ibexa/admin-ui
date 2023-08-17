<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Admin\ContentType;

use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

abstract class AbstractContentTypeRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Symfony\Component\Form\FormView $contentTypeFormView */
        $contentTypeFormView = $options['form_view'];

        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $itemSaveIdentifier = $this->getItemSaveIdentifier();
        $itemPublishAndEditIdentifier = $this->getItemPublishAndEditIdentifier();
        $itemCancelIdentifier = $this->getItemCancelIdentifier();

        $publishItem = $this->createMenuItem(
            $itemSaveIdentifier,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => sprintf('#%s', $contentTypeFormView['publishContentType']->vars['id']),
                ],
            ]
        );

        $publishAndEditItem = $this->createMenuItem(
            $itemPublishAndEditIdentifier,
            [
                'attributes' => [
                    'class' => 'ibexa-btn--trigger',
                    'data-click' => sprintf('#%s', $contentTypeFormView['publishAndEditContentType']->vars['id']),
                ],
            ]
        );

        $publishItem->addChild($publishAndEditItem);

        $menu->setChildren([
            $itemSaveIdentifier => $publishItem,
            $itemCancelIdentifier => $this->createMenuItem(
                $itemCancelIdentifier,
                [
                    'attributes' => [
                        'class' => 'ibexa-btn--trigger',
                        'data-click' => sprintf('#%s', $contentTypeFormView['removeDraft']->vars['id']),
                    ],
                ]
            ),
        ]);

        return $menu;
    }

    abstract public function getItemSaveIdentifier(): string;

    abstract public function getItemPublishAndEditIdentifier(): string;

    abstract public function getItemCancelIdentifier(): string;
}
