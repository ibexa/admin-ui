<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer to deal with translatable properties, where values are indexed by language code.
 *
 * @phpstan-type TPropertyType mixed
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<array<string, TPropertyType>, TPropertyType>
 */
class TranslatablePropertyTransformer implements DataTransformerInterface
{
    /**
     * Current language code (e.g. eng-GB).
     */
    private string $languageCode;

    public function __construct(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function transform(mixed $value): mixed
    {
        if (!($value && isset($value[$this->languageCode]))) {
            return null;
        }

        return $value[$this->languageCode];
    }

    /**
     * @phpstan-return array<string, TPropertyType>
     */
    public function reverseTransform(mixed $value): array
    {
        $value = (false === $value || [] === $value) ? null : $value;

        /** @phpstan-var TPropertyType */
        return [$this->languageCode => $value];
    }
}
