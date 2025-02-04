<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * @template-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
 */
final class ContentValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_CONTENT_ID = 'contentId';
    private const string ATTRIBUTE_VERSION_NO = 'versionNo';
    private const string ATTRIBUTE_LANGUAGE_CODE = 'languageCode';

    public function __construct(
        private readonly ContentService $contentService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [
            self::ATTRIBUTE_CONTENT_ID,
            self::ATTRIBUTE_VERSION_NO,
            self::ATTRIBUTE_LANGUAGE_CODE,
        ];
    }

    protected function getClass(): string
    {
        return Content::class;
    }

    protected function validateKey(array $key): bool
    {
        if (!array_key_exists(self::ATTRIBUTE_CONTENT_ID, $key)) {
            return false;
        }
        $contentId = $key[self::ATTRIBUTE_CONTENT_ID];
        if (!is_numeric($contentId)) {
            return false;
        }

        if (array_key_exists(self::ATTRIBUTE_VERSION_NO, $key) && !is_numeric($key[self::ATTRIBUTE_VERSION_NO])) {
            return false;
        }

        if (!is_array($key[self::ATTRIBUTE_LANGUAGE_CODE])) {
            return false;
        }

        return true;
    }

    protected function load(array $key): object
    {
        $contentId = (int)$key[self::ATTRIBUTE_CONTENT_ID];
        $languages = $key[self::ATTRIBUTE_LANGUAGE_CODE];
        $versionNo = $key[self::ATTRIBUTE_VERSION_NO] ?? null;
        if ($versionNo !== null) {
            $versionNo = (int)$versionNo;
        }

        return $this->contentService->loadContent($contentId, $languages, $versionNo);
    }
}
