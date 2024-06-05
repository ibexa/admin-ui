<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\Content\VersionId;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;

/**
 * @deprecated Please move to use ContentDraftListDataset to get a paginated list of content drafts
 */
class ContentDraftsDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var array */
    private $data = [];

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User|null $user
     *
     * @return \Ibexa\AdminUi\UI\Dataset\ContentDraftsDataset
     */
    public function load(User $user = null): self
    {
        try {
            $contentDrafts = $this->contentService->loadContentDrafts($user);
        } catch (UnauthorizedException $e) {
            // if user has no access content/versionread for one of versions, exception is caught and draft array is empty
            $contentDrafts = [];
        }

        $contentDrafts = array_filter($contentDrafts, static function (VersionInfo $version) {
            // Filter out content that has been sent to trash
            return !$version->getContentInfo()->isTrashed();
        });

        $contentTypes = $contentTypeIds = [];
        foreach ($contentDrafts as $contentDraft) {
            $contentTypeIds[] = $contentDraft->getContentInfo()->contentTypeId;
        }

        if (!empty($contentTypeIds)) {
            $contentTypes = $this->contentTypeService->loadContentTypeList(array_unique($contentTypeIds));
        }

        // ContentService::loadContentDrafts returns unsorted list of VersionInfo.
        // Sort results by modification date, descending.
        usort($contentDrafts, static function (VersionInfo $a, VersionInfo $b) {
            return $b->modificationDate <=> $a->modificationDate;
        });

        $this->data = array_map(
            function (VersionInfo $versionInfo) use ($contentTypes) {
                return $this->mapContentDraft(
                    $versionInfo,
                    $contentTypes[$versionInfo->getContentInfo()->contentTypeId]
                );
            },
            $contentDrafts
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getContentDrafts(): array
    {
        return $this->data;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $draft
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return array
     */
    private function mapContentDraft(VersionInfo $draft, ContentType $contentType): array
    {
        $contentInfo = $draft->getContentInfo();

        return [
            'id' => new VersionId(
                $contentInfo->id,
                $draft->versionNo
            ),
            'contentId' => $contentInfo->id,
            'name' => $draft->getName(),
            'type' => $contentType->getName(),
            'content_type' => $contentType,
            'language' => $draft->initialLanguageCode,
            'version' => $draft->versionNo,
            'modified' => $draft->modificationDate,
        ];
    }
}
