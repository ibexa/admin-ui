<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\FieldTypeToolbar;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbar;
use Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbarItem;
use Ibexa\Core\FieldType\FieldTypeRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
final class FieldTypeToolbarFactory
{
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    private FieldTypeRegistry $fieldTypeRegistry;

    private TranslatorInterface $translator;

    public function __construct(
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver,
        FieldTypeRegistry $fieldTypeRegistry,
        TranslatorInterface $translator
    ) {
        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->translator = $translator;
    }

    public function create(): FieldTypeToolbar
    {
        $items = [];
        foreach ($this->getAvailableFieldTypes() as $fieldType) {
            $items[] = new FieldTypeToolbarItem(
                $fieldType->getFieldTypeIdentifier(),
                $this->getFieldTypeLabel($fieldType->getFieldTypeIdentifier()),
                $fieldType->isSingular()
            );
        }

        usort($items, static function (FieldTypeToolbarItem $a, FieldTypeToolbarItem $b): int {
            return strcmp($a->getName(), $b->getName());
        });

        return new FieldTypeToolbar($items);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\FieldType[]
     */
    private function getAvailableFieldTypes(): iterable
    {
        $excludedFieldTypes = $this->contentTypeFieldTypesResolver->getFieldTypes();

        foreach ($this->fieldTypeRegistry->getConcreteFieldTypesIdentifiers() as $identifier) {
            if (!array_key_exists($identifier, $excludedFieldTypes)) {
                yield $this->fieldTypeRegistry->getFieldType($identifier);
            }
        }
    }

    /**
     * Generate a human-readable name for field type identifier.
     */
    private function getFieldTypeLabel(string $fieldTypeIdentifier): string
    {
        return $this->translator->trans(/** @Ignore */
            $fieldTypeIdentifier . '.name',
            [],
            'fieldtypes'
        );
    }
}
