<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\FieldType\FieldValueTransformer;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\FieldType;
use PHPUnit\Framework\TestCase;
use stdClass;

class FieldValueTransformerTest extends TestCase
{
    public function testTransformNull()
    {
        $value = new stdClass();

        $fieldType = $this->createMock(FieldType::class);
        $fieldType
            ->expects(self::never())
            ->method('toHash');

        $result = (new FieldValueTransformer($fieldType))->transform($value);

        self::assertNull($result);
    }

    public function testTransform()
    {
        $value = $this->createMock(Value::class);
        $valueHash = ['lorem' => 'Lorem ipsum dolor...'];

        $fieldType = $this->createMock(FieldType::class);
        $fieldType
            ->expects(self::once())
            ->method('toHash')
            ->with($value)
            ->willReturn($valueHash);

        $result = (new FieldValueTransformer($fieldType))->transform($value);

        self::assertEquals($result, $valueHash);
    }

    public function testReverseTransformNull()
    {
        $emptyValue = $this->createMock(Value::class);

        $fieldType = $this->createMock(FieldType::class);
        $fieldType
            ->expects(self::once())
            ->method('getEmptyValue')
            ->willReturn($emptyValue);
        $fieldType
            ->expects(self::never())
            ->method('fromHash');

        $result = (new FieldValueTransformer($fieldType))->reverseTransform(null);

        self::assertSame($emptyValue, $result);
    }

    public function testReverseTransform()
    {
        $value = 'Lorem ipsum dolor...';
        $expected = $this->createMock(Value::class);

        $fieldType = $this->createMock(FieldType::class);
        $fieldType
            ->expects(self::never())
            ->method('getEmptyValue');
        $fieldType
            ->expects(self::once())
            ->method('fromHash')
            ->willReturn($expected);

        $result = (new FieldValueTransformer($fieldType))->reverseTransform($value);

        self::assertSame($expected, $result);
    }
}

class_alias(FieldValueTransformerTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\DataTransformer\FieldValueTransformerTest');
