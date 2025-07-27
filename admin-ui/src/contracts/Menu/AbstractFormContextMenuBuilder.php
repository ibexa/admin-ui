<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use JMS\TranslationBundle\Model\Message;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFormContextMenuBuilder extends AbstractBuilder
{
    private string $formName;

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        string $formName
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->formName = $formName;
    }

    /**
     * @return string should be lowercase alphanumeric + underscore (a-zA-Z0-9_) string
     */
    abstract protected static function getSidebarType(): string;

    protected static function getSidebarActionMessage(): string
    {
        return 'Submit';
    }

    protected function getConfigureEventName(): string
    {
        return sprintf(
            'ibexa_admin_ui.menu_configure.%s_%s_sidebar_right',
            static::getSidebarType(),
            $this->formName,
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    protected function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $menu->addChild(
            $this->createMenuItem(
                $this->getActionItemId(),
                [
                    'label' => self::getActionLabel(),
                    'attributes' => [
                        'class' => 'ibexa-btn--trigger',
                        'data-click' => $options['submit_selector'] ?? '#',
                    ],
                    'translation_domain' => 'ibexa_menu',
                ]
            )
        );

        $menu->addChild(
            $this->createMenuItem(
                $this->getCancelItemId(),
                [
                    'label' => self::getCancelLabel(),
                    'route' => $options['cancel_route'] ?? null,
                    'routeParameters' => $options['cancel_route_params'] ?? [],
                    'translation_domain' => 'ibexa_menu',
                ]
            )
        );

        return $menu;
    }

    private function getActionItemId(): string
    {
        return sprintf('%s__sidebar_right__%s', $this->formName, static::getSidebarType());
    }

    private function getCancelItemId(): string
    {
        return sprintf('%s__sidebar_right__cancel', $this->formName);
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        $actionLabel = self::getActionLabel();
        $cancelLabel = self::getCancelLabel();

        return [
            (new Message($actionLabel, 'ibexa_menu'))->setDesc(static::getSidebarActionMessage()),
            (new Message($cancelLabel, 'ibexa_menu'))->setDesc('Discard'),
        ];
    }

    private static function getActionLabel(): string
    {
        return sprintf(
            '%s_form__sidebar_right__%s',
            static::getSidebarType(),
            static::getSidebarType(),
        );
    }

    private static function getCancelLabel(): string
    {
        return sprintf(
            '%s_form__sidebar_right__cancel',
            static::getSidebarType(),
        );
    }
}
