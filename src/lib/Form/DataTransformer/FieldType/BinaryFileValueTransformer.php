<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\BinaryFile\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for ibexa_binaryfile field type.
 *
 * {@inheritdoc}
 */
class BinaryFileValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            ['downloadCount' => $value->downloadCount]
        );
    }

    public function reverseTransform(mixed $value): Value
    {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        return $valueObject;
    }
}
