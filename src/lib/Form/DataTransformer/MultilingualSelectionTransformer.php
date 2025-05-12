<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @phpstan-template TValue array
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<TValue, array<string, TValue>>
 */
class MultilingualSelectionTransformer implements DataTransformerInterface
{
    protected string $languageCode;

    private FieldDefinitionData $data;

    public function __construct(string $languageCode, FieldDefinitionData $data)
    {
        $this->languageCode = $languageCode;
        $this->data = $data;
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

    /**
     * @phpstan-return array<string, TValue>
     */
    public function reverseTransform(mixed $value): array
    {
        if (!$value) {
            /** @phpstan-var array<string, TValue> */
            return [
                $this->languageCode => [],
            ];
        }

        /** @phpstan-var array<string, TValue> */
        return array_merge(
            $this->data->fieldSettings['multilingualOptions'],
            [
                $this->languageCode => $value,
            ]
        );
    }
}
