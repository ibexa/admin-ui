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
    /** @phpstan-var TPermissionRestrictions|null */
    private ?array $permissions;

    /** @var string[] */
    private array $previewableTranslations;

    /**
     * @phpstan-param TPermissionRestrictions|null $permissions
     *
     * @param string[] $previewableTranslation
     */
    public function __construct(
        ?array $permissions = null,
        array $previewableTranslation = []
    ) {
        $this->permissions = $permissions;
        $this->previewableTranslations = $previewableTranslation;
    }

    /**
     * @return TPermissionRestrictions|null
     */
    public function getPermissionRestrictions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @return string[]
     */
    public function getPreviewableTranslations(): array
    {
        return $this->previewableTranslations;
    }
}
