<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\Image\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for ibexa_image field type.
 *
 * {@inheritdoc}
 */
final class ImageValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param \Ibexa\Core\FieldType\Image\Value|null $value
     *
     * @return array<string, mixed>
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            ['alternativeText' => $value->alternativeText]
        );
    }

    /**
     * @param array<string, mixed> $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): Value
    {
        /** @var \Ibexa\Core\FieldType\Image\Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        $valueObject->alternativeText = $value['alternativeText'];

        return $valueObject;
    }
}
