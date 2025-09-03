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
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ContentEditAnchorMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__CONTENT = 'content_edit__anchor_menu__content';
    public const string ITEM__META = 'content_edit__anchor_menu__meta';

    private const int ITEM_ORDER_SPAN = 10;

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        private readonly ConfigResolverInterface $configResolver
    ) {
        parent::__construct($factory, $eventDispatcher);
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
        $fieldTypeSettings = $this->configResolver->getParameter(
            'admin_ui_forms.content_edit.fieldtypes'
        );

        $metaFieldTypeIdentifiers = array_keys(array_filter(
            $fieldTypeSettings,
            static fn (array $config): bool => true === $config['meta']
        ));

        $metaFieldGroups = $this->configResolver->getParameter(
            'admin_ui_forms.content_edit.meta_field_groups_list'
        );
        $metaFieldDefinitionCollection = $contentType->getFieldDefinitions()->filter(
            static fn (FieldDefinition $field): bool => in_array($field->getFieldGroup(), $metaFieldGroups, true),
        );

        $items = [];
        $order = 0;
        foreach ($metaFieldDefinitionCollection as $fieldDefinition) {
            $order += self::ITEM_ORDER_SPAN;
            $items[$fieldDefinition->getIdentifier()] = $this->createSecondLevelItem(
                $fieldDefinition->getIdentifier(),
                $fieldDefinition,
                $order
            );
        }

        foreach ($metaFieldTypeIdentifiers as $metaFieldTypeIdentifier) {
            if (false === $contentType->hasFieldDefinitionOfType($metaFieldTypeIdentifier)) {
                continue;
            }

            $fieldDefinitions = $contentType->getFieldDefinitionsOfType($metaFieldTypeIdentifier);
            foreach ($fieldDefinitions as $fieldDefinition) {
                $fieldDefIdentifier = $fieldDefinition->getIdentifier();
                $order += self::ITEM_ORDER_SPAN;
                $items[$fieldDefIdentifier] ??= $this->createSecondLevelItem(
                    $fieldDefIdentifier,
                    $fieldDefinition,
                    $order
                );
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
            (new Message(self::ITEM__CONTENT, 'ibexa_menu'))->setDesc('Content'),
            (new Message(self::ITEM__META, 'ibexa_menu'))->setDesc('Meta'),
        ];
    }
}
