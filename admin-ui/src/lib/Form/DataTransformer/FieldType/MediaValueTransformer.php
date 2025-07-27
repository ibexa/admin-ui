<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\Media\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for ibexa_media field type.
 *
 * @phpstan-type TMediaProperties = array{hasController: bool, loop: bool, autoplay: bool, width: int, height: int}
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Media\Value, TMediaProperties>
 */
class MediaValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-return TMediaProperties
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        /** @phpstan-var TMediaProperties */
        return array_merge(
            $this->getDefaultProperties() ?? [],
            [
                'hasController' => $value->hasController,
                'loop' => $value->loop,
                'autoplay' => $value->autoplay,
                'width' => $value->width,
                'height' => $value->height,
            ]
        );
    }

    /**
     * @phpstan-param TMediaProperties $value
     */
    public function reverseTransform(mixed $value): Value
    {
        /** @var \Ibexa\Core\FieldType\Media\Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        $valueObject->hasController = $value['hasController'];
        $valueObject->loop = $value['loop'];
        $valueObject->autoplay = $value['autoplay'];
        $valueObject->width = $value['width'];
        $valueObject->height = $value['height'];

        return $valueObject;
    }
}
