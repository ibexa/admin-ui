<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\Data;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;

class FieldDefinitionDataTest extends TestCase
{
    public function testFieldDefinition(): void
    {
        $fieldDefinition = $this->getMockForAbstractClass(FieldDefinition::class);
        $data = new FieldDefinitionData(['fieldDefinition' => $fieldDefinition]);
        self::assertSame($fieldDefinition, $data->fieldDefinition);
    }

    public function testGetFieldTypeIdentifier(): void
    {
        $fieldTypeIdentifier = 'ibexa_string';
        $fieldDefinition = $this->getMockBuilder(FieldDefinition::class)
            ->setConstructorArgs([['fieldTypeIdentifier' => $fieldTypeIdentifier]])
            ->getMockForAbstractClass();
        $data = new FieldDefinitionData(['fieldDefinition' => $fieldDefinition]);
        self::assertSame($fieldTypeIdentifier, $data->getFieldTypeIdentifier());
    }
}
