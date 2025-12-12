<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as APIVersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\Content\VersionInfo as CoreVersionInfo;

/**
 * Extends original value object in order to provide additional fields.
 * Takes a standard VersionInfo instance and retrieves properties from it in addition to the provided properties.
 */
class VersionInfo extends CoreVersionInfo
{
    protected ?User $author;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language[] */
    protected array $translations;

    protected bool $userCanRemove;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly APIVersionInfo $versionInfo,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($versionInfo) + $properties);
    }

    public function canDelete(): bool
    {
        return $this->userCanRemove;
    }
}
