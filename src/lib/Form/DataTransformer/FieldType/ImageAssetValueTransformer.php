<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\ImageAsset\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ImageAssetValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param \Ibexa\Core\FieldType\ImageAsset\Value|null $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return array<string, mixed>|null
     */
    public function transform(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Value) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of %s', gettype($value), Value::class)
            );
        }

        return array_merge(
            $this->getDefaultProperties(),
            [
                'destinationContentId' => $value->destinationContentId,
                'alternativeText' => $value->alternativeText,
            ]
        );
    }

    /**
     * @param array<string, mixed>|null $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of an array', gettype($value))
            );
        }

        return new Value($value['destinationContentId'], $value['alternativeText']);
    }
}
