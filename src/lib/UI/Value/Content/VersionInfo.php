<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as APIVersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\Content\VersionInfo as CoreVersionInfo;

/**
 * Extends original value object in order to provide additional fields.
 * Takes a standard VersionInfo instance and retrieves properties from it in addition to the provided properties.
 */
class VersionInfo extends CoreVersionInfo
{
    /** @var User */
    protected $author;

    /**
     * @var Language[]
     */
    protected $translations;

    /**
     * User can remove.
     *
     * @var bool
     */
    protected $userCanRemove;

    /**
     * @param APIVersionInfo $versionInfo
     * @param array $properties
     */
    public function __construct(
        APIVersionInfo $versionInfo,
        array $properties = []
    ) {
        parent::__construct(get_object_vars($versionInfo) + $properties);
    }

    /**
     * Can delete version.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->userCanRemove;
    }
}

class_alias(VersionInfo::class, 'EzSystems\EzPlatformAdminUi\UI\Value\Content\VersionInfo');
