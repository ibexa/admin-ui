<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\BinaryFile\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for ibexa_binaryfile field type.
 *
 * {@inheritdoc}
 */
final class BinaryFileValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
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
        return $this->getReverseTransformedValue($value);
    }
}
