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
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ContentTypeEditAnchorMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM__META = 'content_type_edit__anchor_menu__meta';

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

        $metaFields = $this->getMetaFieldItems($contentTypeDraft);

        if (!empty($metaFields)) {
            $items[self::ITEM__META] = $this->createMenuItem(
                self::ITEM__META,
                [
                    'attributes' => ['data-target-id' => 'ibexa-edit-content-type-sections-meta'],
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
     * @return array<\Knp\Menu\ItemInterface>
     */
    private function getMetaFieldItems(ContentTypeDraft $contentType): array
    {
        $metaFieldTypeIdentifiers = $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers();

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
            (new Message(self::ITEM__META, 'menu'))->setDesc('Meta'),
        ];
    }
}
