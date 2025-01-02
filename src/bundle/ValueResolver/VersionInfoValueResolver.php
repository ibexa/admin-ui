<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo>
 */
final class VersionInfoValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_VERSION_NO = 'versionNo';
    private const string ATTRIBUTE_CONTENT_ID = 'contentId';

    public function __construct(
        private readonly ContentService $contentService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_VERSION_NO, self::ATTRIBUTE_CONTENT_ID];
    }

    protected function getClass(): string
    {
        return VersionInfo::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    protected function load(array $key): object
    {
        $contentId = (int)$key[self::ATTRIBUTE_CONTENT_ID];
        $versionNo = (int)$key[self::ATTRIBUTE_VERSION_NO];

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        return $this->contentService->loadVersionInfo($contentInfo, $versionNo);
    }
}
