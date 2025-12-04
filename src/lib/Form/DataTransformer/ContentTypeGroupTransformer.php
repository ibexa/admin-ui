<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Translates ContentTypeGroup's ID to domain specific ContentTypeGroup object.
 */
final readonly class ContentTypeGroupTransformer implements DataTransformerInterface
{
    public function __construct(private ContentTypeService $contentTypeService)
    {
    }

    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ContentTypeGroup) {
            throw new TransformationFailedException('Expected a ' . ContentTypeGroup::class . ' object.');
        }

        return $value->id;
    }

    /**
     * @param int|string|null $value
     */
    public function reverseTransform($value): ?ContentTypeGroup
    {
        if (empty($value)) {
            return null;
        }

        if (!is_int($value) && !ctype_digit($value)) {
            throw new TransformationFailedException('Expected a numeric string.');
        }

        try {
            return $this->contentTypeService->loadContentTypeGroup((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
