<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

class Node extends RestValue
{
    private int $depth;

    public int $locationId;

    public int $contentId;

    public int $versionNo;

    public string $name;

    public string $contentTypeIdentifier;

    public bool $isContainer;

    public bool $isInvisible;

    public int $displayLimit;

    public int $totalChildrenCount;

    public int $reverseRelationsCount;

    public bool $isBookmarked;

    /** @var \Ibexa\AdminUi\REST\Value\ContentTree\Node[] */
    public array $children;

    public string $pathString;

    public string $mainLanguageCode;

    /**
     * @param array<\Ibexa\AdminUi\REST\Value\ContentTree\Node> $children
     */
    public function __construct(
        int $depth,
        int $locationId,
        int $contentId,
        int $versionNo,
        string $name,
        string $contentTypeIdentifier,
        bool $isContainer,
        bool $isInvisible,
        int $displayLimit,
        int $totalChildrenCount,
        int $reverseRelationsCount,
        bool $isBookmarked,
        string $mainLanguageCode,
        array $children = [],
        string $pathString = ''
    ) {
        $this->depth = $depth;
        $this->locationId = $locationId;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
        $this->name = $name;
        $this->isInvisible = $isInvisible;
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->isContainer = $isContainer;
        $this->totalChildrenCount = $totalChildrenCount;
        $this->displayLimit = $displayLimit;
        $this->reverseRelationsCount = $reverseRelationsCount;
        $this->isBookmarked = $isBookmarked;
        $this->children = $children;
        $this->pathString = $pathString;
        $this->mainLanguageCode = $mainLanguageCode;
    }
}
