<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

final class Node extends RestValue
{
    /**
     * @param array<\Ibexa\AdminUi\REST\Value\ContentTree\Node> $children
     */
    public function __construct(
        public int $locationId,
        public int $contentId,
        public int $versionNo,
        public string $name,
        public string $contentTypeIdentifier,
        public bool $isContainer,
        public bool $isInvisible,
        public bool $isHidden,
        public int $displayLimit,
        public int $totalChildrenCount,
        public int $reverseRelationsCount,
        public bool $isBookmarked,
        public string $mainLanguageCode,
        public array $children = [],
        public string $pathString = ''
    ) {
    }
}
