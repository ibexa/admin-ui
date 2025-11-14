<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Version;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

class VersionHasConflict extends AbstractSpecification
{
    /** @var ContentService */
    private $contentService;

    /** @var string */
    private $languageCode;

    /**
     * @param ContentService $contentService
     * @param string $languageCode
     */
    public function __construct(
        ContentService $contentService,
        string $languageCode
    ) {
        $this->contentService = $contentService;
        $this->languageCode = $languageCode;
    }

    /**
     * Checks if $content has version conflict.
     *
     * @param VersionInfo $versionInfo
     *
     * @return bool
     *
     * @throws UnauthorizedException
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
