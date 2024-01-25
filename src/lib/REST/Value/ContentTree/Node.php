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
    /** @var int */
    private $depth;

    /** @var int */
    public $locationId;

    /** @var int */
    public $contentId;

    public int $versionNo;

    /** @var string[] */
    public array $translations;

    /** @var string[] */
    public array $previewableTranslations;

    /** @var string */
    public $name;

    /** @var string */
    public $contentTypeIdentifier;

    /** @var bool */
    public $isContainer;

    /** @var bool */
    public $isInvisible;

    /** @var int */
    public $displayLimit;

    /** @var int */
    public $totalChildrenCount;

    public int $reverseRelationsCount;

    public bool $isBookmarked;

    /** @var \Ibexa\AdminUi\REST\Value\ContentTree\Node[] */
    public $children;

    public string $pathString;

    /**
     * @param int $depth
     * @param int $locationId
     * @param int $contentId
     * @param string[] $translations
     * @param string[] $previewableTranslations
     * @param string $name
     * @param string $contentTypeIdentifier
     * @param bool $isContainer
     * @param bool $isInvisible
     * @param int $displayLimit
     * @param int $totalChildrenCount
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\Node[] $children
     */
    public function __construct(
        int $depth,
        int $locationId,
        int $contentId,
        int $versionNo,
        array $translations,
        array $previewableTranslations,
        string $name,
        string $contentTypeIdentifier,
        bool $isContainer,
        bool $isInvisible,
        int $displayLimit,
        int $totalChildrenCount,
        int $reverseRelationsCount,
        bool $isBookmarked,
        array $children = [],
        string $pathString = ''
    ) {
        $this->depth = $depth;
        $this->locationId = $locationId;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
        $this->translations = $translations;
        $this->previewableTranslations = $previewableTranslations;
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
    }
}

class_alias(Node::class, 'EzSystems\EzPlatformAdminUi\REST\Value\ContentTree\Node');
