<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>
 */
final class ContentTypeFromIdValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_CONTENT_TYPE_ID = 'contentTypeId';

    public function __construct(
        private readonly ContentTypeService $contentTypeService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_CONTENT_TYPE_ID];
    }

    protected function getClass(): string
    {
        return ContentType::class;
    }

    protected function load(array $key): object
    {
        return $this->contentTypeService->loadContentType(
            (int)$key[self::ATTRIBUTE_CONTENT_TYPE_ID]
        );
    }
}
