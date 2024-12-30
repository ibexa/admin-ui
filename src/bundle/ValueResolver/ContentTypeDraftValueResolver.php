<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft>
 */
final class ContentTypeDraftValueResolver extends AbstractValueResolver
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
        return ContentTypeDraft::class;
    }

    protected function load(array $key): object
    {
        return $this->contentTypeService->loadContentTypeDraft(
            (int)$key[self::ATTRIBUTE_CONTENT_TYPE_ID]
        );
    }
}
