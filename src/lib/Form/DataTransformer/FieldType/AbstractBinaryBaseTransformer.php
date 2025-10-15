<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Core\FieldType\Value;

/**
 * Base transformer for binary file based field types.
 *
 * {@inheritdoc}
 */
abstract class AbstractBinaryBaseTransformer
{
    public function __construct(
        protected FieldType $fieldType,
        protected Value $initialValue,
        protected string $valueClass
    ) {
    }

    /**
     * @return array<string, false|null>
     */
    public function getDefaultProperties(): array
    {
        return [
            'file' => null,
            'remove' => false,
        ];
    }

    /**
     * @param array<string, mixed> $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function getReverseTransformedValue(array $value): mixed
    {
        if ($value['remove']) {
            return $this->fieldType->getEmptyValue();
        }

        /* in case file is not modified, overwrite settings only */
        if (null === $value['file']) {
            return clone $this->initialValue;
        }

        $properties = [
            'inputUri' => $value['file']->getRealPath(),
            'fileName' => $value['file']->getClientOriginalName(),
            'fileSize' => $value['file']->getSize(),
        ];

        return new $this->valueClass($properties);
    }
}
