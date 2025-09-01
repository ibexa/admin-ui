<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\Relation\Value;
use Symfony\Component\Form\DataTransformerInterface;

final readonly class RelationValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?int
    {
        if (!$value instanceof Value) {
            return null;
        }

        if ($value->destinationContentId === null) {
            return null;
        }

        return $value->destinationContentId;
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (!is_numeric($value)) {
            return null;
        }

        return new Value($value);
    }
}
