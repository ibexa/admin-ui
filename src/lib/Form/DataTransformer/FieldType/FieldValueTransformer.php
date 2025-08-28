<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\FieldType;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Generic data transformer for FieldTypes values.
 * Uses FieldType::toHash() / FieldType::fromHash().
 */
final readonly class FieldValueTransformer implements DataTransformerInterface
{
    public function __construct(private FieldType $fieldType)
    {
    }

    /**
     * Transforms a FieldType Value into a hash using `FieldTpe::toHash()`.
     * This hash is compatible with `reverseTransform()`.
     *
     * @return array<mixed>|null the value's hash, or null if $value was not a FieldType Value
     */
    public function transform(mixed $value): ?array
    {
        if (!$value instanceof Value) {
            return null;
        }

        return $this->fieldType->toHash($value);
    }

    /**
     * Transforms a hash into a FieldType Value using `FieldType::fromHash()`.
     * The FieldValue is compatible with `transform()`.
     */
    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        return $this->fieldType->fromHash($value);
    }
}
