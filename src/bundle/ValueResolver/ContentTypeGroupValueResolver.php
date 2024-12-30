<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup>
 */
final class ContentTypeGroupValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_CONTENT_TYPE_GROUP_ID = 'contentTypeGroupId';

    public function __construct(
        private readonly ContentTypeService $contentTypeService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_CONTENT_TYPE_GROUP_ID];
    }

    protected function getClass(): string
    {
        return ContentTypeGroup::class;
    }

    protected function load(array $key): object
    {
        return $this->contentTypeService->loadContentTypeGroup(
            (int) $key[self::ATTRIBUTE_CONTENT_TYPE_GROUP_ID]
        );
    }
}
