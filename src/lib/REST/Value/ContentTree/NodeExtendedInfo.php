<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

/**
 * @phpstan-type TRestrictions array{
 *      hasAccess: bool,
 *      restrictedContentTypeIds?: array<int>,
 *      restrictedLanguageCodes?: array<string>,
 * }
 * @phpstan-type TPermissionRestrictions array{
 *      create: TRestrictions,
 *      edit: TRestrictions,
 *      delete: TRestrictions,
 *      hide: TRestrictions,
 * }
 */
final class NodeExtendedInfo extends RestValue
{
    /**
     * @phpstan-param TPermissionRestrictions|null $permissions
     *
     * @param array<int, string> $previewableTranslations
     * @param array<int, string> $translations
     */
    public function __construct(
        private readonly ?array $permissions = null,
        private readonly array $previewableTranslations = [],
        private readonly array $translations = []
    ) {
    }

    /**
     * @return TPermissionRestrictions|null
     */
    public function getPermissionRestrictions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @return array<int, string>
     */
    public function getPreviewableTranslations(): array
    {
        return $this->previewableTranslations;
    }

    /**
     * @return array<int, string>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
