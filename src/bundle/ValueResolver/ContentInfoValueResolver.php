<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo>
 */
final class ContentInfoValueResolver extends AbstractValueResolver
{
    public const string ATTRIBUTE_CONTENT_INFO_ID = 'contentInfoId';

    public function __construct(
        private readonly ContentService $contentService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_CONTENT_INFO_ID];
    }

    protected function getClass(): string
    {
        return ContentInfo::class;
    }

    protected function load(array $key): object
    {
        return $this->contentService->loadContentInfo(
            (int)$key[self::ATTRIBUTE_CONTENT_INFO_ID]
        );
    }
}
