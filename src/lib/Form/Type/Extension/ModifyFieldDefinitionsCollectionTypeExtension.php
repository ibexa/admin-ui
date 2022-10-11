<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension;

use Ibexa\AdminUi\Form\Type\ContentType\FieldDefinitionsCollectionType;
use Ibexa\AdminUi\Form\Type\Extension\EventSubscriber\ModifyFieldDefinitionFieldsSubscriber;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Extension for Field Definition list in Content Type editing form.
 *
 * Hooks up event subscriber used to enforce modifying fields for given field type.
 */
final class ModifyFieldDefinitionsCollectionTypeExtension extends AbstractTypeExtension
{
    private string $fieldTypeIdentifier;

    /** @var array<string, mixed> */
    private array $modifiedOptions;

    /**
     * @param string $fieldTypeIdentifier
     * @param array<string, mixed> $modifiedOptions
     */
    public function __construct(string $fieldTypeIdentifier, array $modifiedOptions)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->modifiedOptions = $modifiedOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subscriber = new ModifyFieldDefinitionFieldsSubscriber($this->fieldTypeIdentifier, $this->modifiedOptions);

        foreach ($builder->all() as $fieldTypeGroup) {
            $fieldTypeGroup->addEventSubscriber($subscriber);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [FieldDefinitionsCollectionType::class];
    }
}
