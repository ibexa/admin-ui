<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Version;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class VersionHasConflict extends AbstractSpecification
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly string $languageCode
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function isSatisfiedBy(mixed $versionInfo): bool
    {
        $versions = $this->contentService->loadVersions($versionInfo->getContentInfo());

        foreach ($versions as $checkedVersionInfo) {
            if ($checkedVersionInfo->getVersionNo() > $versionInfo->getVersionNo()
                && $checkedVersionInfo->isPublished()
                && $checkedVersionInfo->initialLanguageCode === $this->languageCode
            ) {
                return true;
            }
        }

        return false;
    }
}
