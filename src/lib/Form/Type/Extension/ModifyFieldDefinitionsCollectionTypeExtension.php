<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension;

use Ibexa\AdminUi\Form\Type\ContentType\FieldDefinitionsCollectionType;
use Ibexa\AdminUi\Form\Type\Extension\EventSubscriber\ModifyFieldDefinitionFieldsSubscriber;
use Ibexa\Contracts\Core\Specification\SpecificationInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Extension for Field Definition list in content type editing form.
 *
 * Hooks up event subscriber used to enforce modifying fields for given field type.
 */
final class ModifyFieldDefinitionsCollectionTypeExtension extends AbstractTypeExtension
{
    /**
     * @param array<string, mixed> $modifiedOptions
     * @param array<string> $fieldIdentifiers
     */
    public function __construct(
        private readonly array $modifiedOptions,
        private readonly array $fieldIdentifiers = [],
        private readonly ?string $fieldTypeIdentifier = null,
        private readonly ?SpecificationInterface $contentTypeSpecification = null
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subscriber = new ModifyFieldDefinitionFieldsSubscriber(
            $this->modifiedOptions,
            $this->fieldIdentifiers,
            $this->fieldTypeIdentifier,
            $this->contentTypeSpecification
        );

        foreach ($builder->all() as $fieldTypeGroup) {
            $fieldTypeGroup->addEventSubscriber($subscriber);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [FieldDefinitionsCollectionType::class];
    }
}
