<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\AdminUi\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractActionBuilder extends AbstractBuilder
{
    private FactoryInterface $menuItemFactory;

    private TranslatorInterface $translator;

    protected ContentAwareActionItemFactoryInterface $contentAwareActionItemFactory;

    public function __construct(
        MenuItemFactoryInterface $menuItemFactory,
        EventDispatcherInterface $eventDispatcher,
        ContentAwareActionItemFactoryInterface $contentAwareActionItemFactory,
        TranslatorInterface $translator
    ) {
        parent::__construct($menuItemFactory, $eventDispatcher);

        $this->menuItemFactory = $menuItemFactory;
        $this->translator = $translator;
        $this->contentAwareActionItemFactory = $contentAwareActionItemFactory;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createActionItem(string $name, array $options = []): ItemInterface
    {
        if (empty($options['extras']['translation_domain'])) {
            $options['extras']['translation_domain'] = 'ibexa_action_menu';
        }

        if (empty($options['label'])) {
            $options['label'] = $this->createLabel($name);
        }

        return $this->menuItemFactory->createItem($name, $options);
    }

    protected function createLabel(string $name): string
    {
        return $this->translator->trans(
            $name,
            [],
            'ibexa_action_menu'
        );
    }
}
