<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentEditAnchorMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM__CONTENT = 'content_edit__anchor_menu__content';
    public const ITEM__META = 'content_edit__anchor_menu__meta';

    private const ITEM_ORDER_SPAN = 10;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        ConfigResolverInterface $configResolver
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->configResolver = $configResolver;
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_EDIT_ANCHOR_MENU;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        /** @var \Ibexa\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $options['content_type'];

        /** @var array<string, array<string>> $groupedFields */
        $groupedFields = $options['grouped_fields'];

        $items = [
            self::ITEM__CONTENT => $this->createMenuItem(
                self::ITEM__CONTENT,
                [
                    'attributes' => ['data-target-id' => 'ibexa-edit-content-sections-content-fields'],
                    'extras' => [
                        'orderNumber' => 10,
                    ],
                ]
            ),
        ];

        $items[self::ITEM__CONTENT]->setChildren(
            $this->getContentFieldGroupItems($groupedFields)
        );

        $metaFields = $this->getMetaFieldItems($contentType);

        if (!empty($metaFields)) {
            $items[self::ITEM__META] = $this->createMenuItem(
                self::ITEM__META,
                [
                    'attributes' => ['data-target-id' => 'ibexa-edit-content-sections-meta'],
                    'extras' => [
                        'orderNumber' => 20,
                    ],
                ]
            );

            $items[self::ITEM__META]->setChildren($metaFields);
        }

        $menu->setChildren($items);

        return $menu;
    }

    /**
     * @param array<string, array<string>> $groupedFields
     *
     * @return array<\Knp\Menu\ItemInterface>
     */
    private function getContentFieldGroupItems(array $groupedFields): array
    {
        $items = [];
        $order = 0;
        foreach ($groupedFields as $group => $fields) {
            $order += self::ITEM_ORDER_SPAN;
            $items[$group] = $this->createMenuItem($group, [
                'attributes' => [
                    'data-target-id' => sprintf('ibexa-edit-content-sections-content-fields-%s', str_replace(' ', '-', mb_strtolower($group))),
                ],
                'extras' => [
                    'orderNumber' => $order,
                ],
            ]);
        }

        return $items;
    }

    /**
     * @return array<\Knp\Menu\ItemInterface>
     */
    private function getMetaFieldItems(ContentType $contentType): array
    {
        $fieldTypeSettings = $this->configResolver->getParameter('admin_ui_forms.content_edit.fieldtypes');
        $metaFieldTypeIdentifiers = array_keys(array_filter(
            $fieldTypeSettings,
            static fn (array $config): bool => true === $config['meta']
        ));

        $items = [];
        $order = 0;
        foreach ($metaFieldTypeIdentifiers as $metaFieldTypeIdentifier) {
            if (false === $contentType->hasFieldDefinitionOfType($metaFieldTypeIdentifier)) {
                continue;
            }

            $fieldDefinitions = $contentType->getFieldDefinitionsOfType($metaFieldTypeIdentifier);
            foreach ($fieldDefinitions as $fieldDefinition) {
                $fieldDefIdentifier = $fieldDefinition->identifier;
                $order += self::ITEM_ORDER_SPAN;
                $items[$fieldDefIdentifier] = $this->createSecondLevelItem($fieldDefIdentifier, $fieldDefinition, $order);
            }
        }

        return $items;
    }

    private function createSecondLevelItem(
        string $fieldDefIdentifier,
        FieldDefinition $fieldDefinition,
        int $order
    ): ItemInterface {
        return $this->createMenuItem(
            $fieldDefIdentifier,
            [
                'label' => $fieldDefinition->getName(),
                'attributes' => [
                    'data-target-id' => sprintf('ibexa-edit-content-sections-meta-%s', $fieldDefIdentifier),
                ],
                'extras' => [
                    'orderNumber' => $order,
                ],
            ]
        );
    }

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__CONTENT, 'menu'))->setDesc('Content'),
            (new Message(self::ITEM__META, 'menu'))->setDesc('Meta'),
        ];
    }
}
