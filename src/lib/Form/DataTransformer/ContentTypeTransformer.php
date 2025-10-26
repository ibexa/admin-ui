<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Translates content type's identifier to domain specific ContentType object.
 */
class ContentTypeTransformer implements DataTransformerInterface
{
    /** @var ContentTypeService */
    protected $contentTypeService;

    /**
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param ContentType $value
     *
     * @return string|null
     */
    public function transform($value)
    {
        return null !== $value
            ? $value->identifier
            : null;
    }

    /**
     * @param mixed $value
     *
     * @return ContentType|null
     *
     * @throws NotFoundException
     */
    public function reverseTransform($value)
    {
        return null !== $value && !empty($value)
            ? $this->contentTypeService->loadContentTypeByIdentifier($value)
            : null;
    }
}

class_alias(ContentTypeTransformer::class, 'EzSystems\EzPlatformAdminUi\Form\DataTransformer\ContentTypeTransformer');
