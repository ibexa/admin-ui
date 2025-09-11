<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class ContentDraftHasConflict extends AbstractSpecification
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly string $languageCode
    ) {
    }

    /**
     * Checks if Content has draft conflict.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function isSatisfiedBy(mixed $contentInfo): bool
    {
        $versions = $this->contentService->loadVersions($contentInfo);

        foreach ($versions as $checkedVersionInfo) {
            if ($checkedVersionInfo->isDraft()
                && $checkedVersionInfo->getVersionNo() > $contentInfo->currentVersionNo
                && $checkedVersionInfo->getInitialLanguage()->getLanguageCode() === $this->languageCode
            ) {
                return true;
            }
        }

        return false;
    }
}
