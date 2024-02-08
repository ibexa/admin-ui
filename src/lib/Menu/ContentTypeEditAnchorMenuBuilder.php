<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ContentTypeEditAnchorMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM__GLOBAL_PROPERTIES = 'content_type_edit__anchor_menu__global_properties';
    public const ITEM__FIELD_DEFINITIONS = 'content_type_edit__anchor_menu__field_definitions';

    private const ITEM_ORDER_SPAN = 10;

    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_TYPE_EDIT_ANCHOR_MENU;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        /** @var \Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft */
        $contentTypeDraft = $options['content_type'];

        $items = [
            $this->createMenuItem(
                self::ITEM__GLOBAL_PROPERTIES,
                [
                    'attributes' => ['data-target-id' => 'Global-properties'],
                    'extras' => [
                        'orderNumber' => 10,
                    ],
                ]
            ),
            $this->createMenuItem(
                self::ITEM__FIELD_DEFINITIONS,
                [
                    'attributes' => ['data-target-id' => 'Field-definitions'],
                    'extras' => [
                        'orderNumber' => 20,
                    ],
                ]
            ),
        ];

        $menu->setChildren(
            array_merge(
                $items,
                $this->getMetaFieldItems($contentTypeDraft)
            )
        );

        return $menu;
    }

    /**
     * @return array<\Knp\Menu\ItemInterface>
     */
    private function getMetaFieldItems(ContentTypeDraft $contentType): array
    {
        $metaFieldTypeIdentifiers = $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers();

        $items = [];

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if (!in_array($fieldDefinition->fieldTypeIdentifier, $metaFieldTypeIdentifiers, true)) {
                continue;
            }

            $fieldDefIdentifier = $fieldDefinition->identifier;
            $order = $fieldDefinition->position + self::ITEM_ORDER_SPAN;
            $items[$fieldDefIdentifier] = $this->createMetaMenuItem($fieldDefIdentifier, $fieldDefinition, $order);
        }

        return $items;
    }

    private function createMetaMenuItem(
        string $fieldDefIdentifier,
        FieldDefinition $fieldDefinition,
        int $order
    ): ItemInterface {
        return $this->createMenuItem(
            $fieldDefIdentifier,
            [
                'label' => $fieldDefinition->getName(),
                'attributes' => [
                    'data-target-id' => sprintf('ibexa-edit-content-type-sections-meta-%s', $fieldDefIdentifier),
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
            (new Message(self::ITEM__GLOBAL_PROPERTIES, 'ibexa_menu'))->setDesc('Global properties'),
            (new Message(self::ITEM__FIELD_DEFINITIONS, 'ibexa_menu'))->setDesc('Field definitions'),
        ];
    }
}
