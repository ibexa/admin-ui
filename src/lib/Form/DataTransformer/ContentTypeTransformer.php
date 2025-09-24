<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Translates content type's identifier to domain specific ContentType object.
 */
final readonly class ContentTypeTransformer implements DataTransformerInterface
{
    public function __construct(private ContentTypeService $contentTypeService)
    {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null $value
     */
    public function transform(mixed $value): ?string
    {
        return $value?->getIdentifier();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function reverseTransform(mixed $value): ?ContentType
    {
        return !empty($value)
            ? $this->contentTypeService->loadContentTypeByIdentifier($value)
            : null;
    }
}
