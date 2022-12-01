<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Version;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\ContentService;

class VersionHasConflict extends AbstractSpecification
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var string */
    private $languageCode;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param string $languageCode
     */
    public function __construct(ContentService $contentService, string $languageCode)
    {
        $this->contentService = $contentService;
        $this->languageCode = $languageCode;
    }

    /**
     * Checks if $content has version conflict.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function isSatisfiedBy($versionInfo): bool
    {
        $versions = $this->contentService->loadVersions($versionInfo->getContentInfo());

        foreach ($versions as $checkedVersionInfo) {
            if ($checkedVersionInfo->versionNo > $versionInfo->versionNo
                && $checkedVersionInfo->isPublished()
                && $checkedVersionInfo->initialLanguageCode === $this->languageCode
            ) {
                return true;
            }
        }

        return false;
    }
}

class_alias(VersionHasConflict::class, 'EzSystems\EzPlatformAdminUi\Specification\Version\VersionHasConflict');
