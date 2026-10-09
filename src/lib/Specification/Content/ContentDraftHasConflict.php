<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

class ContentDraftHasConflict extends AbstractSpecification
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
     * Checks if Content has draft conflict.
     *
     * @param ContentInfo $contentInfo
     *
     * @return bool
     *
     * @throws UnauthorizedException
     */
    public function isSatisfiedBy($contentInfo): bool
    {
        $versions = $this->contentService->loadVersions($contentInfo);

        foreach ($versions as $checkedVersionInfo) {
            if ($checkedVersionInfo->isDraft()
                && $checkedVersionInfo->versionNo > $contentInfo->currentVersionNo
                && $checkedVersionInfo->initialLanguageCode === $this->languageCode
            ) {
                return true;
            }
        }

        return false;
    }
}

class_alias(ContentDraftHasConflict::class, 'EzSystems\EzPlatformAdminUi\Specification\Content\ContentDraftHasConflict');
