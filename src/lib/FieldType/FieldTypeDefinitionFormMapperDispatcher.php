<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\FieldType;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Symfony\Component\Form\FormInterface;

/**
 * FieldType mappers dispatcher.
 *
 * Adds the form elements matching the given Field Data (Value or Definition) to a given Form.
 */
class FieldTypeDefinitionFormMapperDispatcher implements FieldTypeDefinitionFormMapperDispatcherInterface
{
    /**
     * FieldType form mappers, indexed by FieldType identifier.
     *
     * @var \Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface[]
     */
    private array $mappers = [];

    /**
     * @param \Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        $this->mappers = $mappers;
    }

    public function addMapper(FieldDefinitionFormMapperInterface $mapper, string $fieldTypeIdentifier): void
    {
        $this->mappers[$fieldTypeIdentifier] = $mapper;
    }

    public function map(FormInterface $fieldForm, FieldDefinitionData $data): void
    {
        $fieldTypeIdentifier = $data->getFieldTypeIdentifier();

        if (!isset($this->mappers[$fieldTypeIdentifier])) {
            return;
        }

        $this->mappers[$fieldTypeIdentifier]->mapFieldDefinitionForm($fieldForm, $data);
    }
}
