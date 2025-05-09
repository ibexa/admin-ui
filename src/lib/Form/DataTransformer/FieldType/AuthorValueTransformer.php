<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\Author\Author;
use Ibexa\Core\FieldType\Author\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Author\Value.
 *
 * @phpstan-type TAuthorProperties = array{id: ?int, name: string, email: string}
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Author\Value, TAuthorProperties[]>
 */
class AuthorValueTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-return TAuthorProperties[]
     */
    public function transform(mixed $value): array
    {
        if (!$value instanceof Value || $value->authors->count() == 0) {
            return [
                [
                    'id' => null,
                    'name' => '',
                    'email' => '',
                ],
            ];
        }

        $authors = [];
        foreach ($value->authors as $author) {
            $authors[] = [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
            ];
        }

        return $authors;
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return null;
        }

        $authors = [];
        foreach ($value as $authorProperties) {
            $authors[] = new Author($authorProperties);
        }

        return new Value($authors);
    }
}
