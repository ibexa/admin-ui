<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a Language's ID and a domain specific Language object.
 */
final readonly class LanguageTransformer implements DataTransformerInterface
{
    public function __construct(private LanguageService $languageService)
    {
    }

    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Language) {
            throw new TransformationFailedException('Expected a ' . Language::class . ' object.');
        }

        return $value->getLanguageCode();
    }

    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException if the value can not be found
     */
    public function reverseTransform(mixed $value): ?Language
    {
        if (empty($value)) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException(
                'Invalid data, expected a string value'
            );
        }

        try {
            return $this->languageService->loadLanguage($value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
