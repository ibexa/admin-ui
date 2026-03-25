<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

final class TranslatedNamesList extends RestValue
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] $versionInfoList
     */
    public function __construct(
        private readonly array $versionInfoList,
    ) {
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public function getVersionInfoList(): array
    {
        return $this->versionInfoList;
    }
}
