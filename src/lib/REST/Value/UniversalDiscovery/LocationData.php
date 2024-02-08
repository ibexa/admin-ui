<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\UniversalDiscovery;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Values\Version;
use Ibexa\Rest\Value;

/**
 * @phpstan-type SubItems array{
 *     locations: array<\Ibexa\Rest\Server\Values\RestLocation>,
 *     totalCount: int,
 *     versions?: array<\Ibexa\Rest\Server\Values\Version>,
 * }
 * @phpstan-type Restrictions array{
 *     hasAccess: bool,
 *     restrictedContentTypeIds: array<int>,
 *     restrictedLanguageCodes: array<string>,
 * }
 * @phpstan-type PermissionRestrictions array{
 *     create: Restrictions,
 *     edit: Restrictions,
 * }
 */
final class LocationData extends Value
{
    /** @phpstan-var SubItems */
    private array $subItems;

    private ?Location $location;

    private ?bool $isBookmarked;

    /** @phpstan-var PermissionRestrictions|null */
    private ?array $permissions;

    private ?Version $version;

    /**
     * @phpstan-param SubItems $subItems
     * @phpstan-param PermissionRestrictions|null $permissions
     */
    public function __construct(
        array $subItems,
        ?Location $location = null,
        ?bool $isBookmarked = null,
        ?array $permissions = null,
        ?Version $version = null
    ) {
        $this->subItems = $subItems;
        $this->location = $location;
        $this->isBookmarked = $isBookmarked;
        $this->permissions = $permissions;
        $this->version = $version;
    }

    /**
     * @phpstan-return SubItems
     */
    public function getSubItems(): array
    {
        return $this->subItems;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function isBookmarked(): ?bool
    {
        return $this->isBookmarked;
    }

    /**
     * @return PermissionRestrictions|null
     */
    public function getPermissionRestrictions(): ?array
    {
        return $this->permissions;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }
}
