<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class ContentTransformer implements DataTransformerInterface
{
    public function __construct(private ContentService $contentService)
    {
    }

    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Content) {
            throw new TransformationFailedException('Expected a ' . Content::class . ' object.');
        }

        return $value->getId();
    }

    /**
     * Transforms a Content's ID integer into a domain specific Content object.
     *
     * @param string|null $value
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function reverseTransform(mixed $value): ?Content
    {
        if (empty($value)) {
            return null;
        }

        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            throw new TransformationFailedException('Expected a numeric string.');
        }

        try {
            return $this->contentService->loadContent((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
