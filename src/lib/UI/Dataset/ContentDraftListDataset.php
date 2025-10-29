<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\Content\ContentDraftInterface;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\ContentDraftListItemInterface;
use Ibexa\Contracts\Core\Repository\Values\User\User;

class ContentDraftListDataset
{
    /** @var ContentService */
    private $contentService;

    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var ContentDraftInterface[] */
    private $data = [];

    /**
     * @param ContentService $contentService
     * @param ContentTypeService $contentTypeService
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        ValueFactory $valueFactory
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->valueFactory = $valueFactory;
    }

    public function load(
        ?User $user = null,
        int $offset = 0,
        int $limit = 10
    ): self {
        $contentDraftListItems = $this->contentService->loadContentDraftList($user, $offset, $limit)->items;

        $contentTypes = $contentTypeIds = [];
        foreach ($contentDraftListItems as $contentDraftListItem) {
            if ($contentDraftListItem->hasVersionInfo()) {
                $contentTypeIds[] = $contentDraftListItem->getVersionInfo()->getContentInfo()->contentTypeId;
            }
        }

        if (!empty($contentTypeIds)) {
            $contentTypes = $this->contentTypeService->loadContentTypeList(array_unique($contentTypeIds));
        }

        $this->data = array_map(
            function (ContentDraftListItemInterface $contentDraftListItem) use ($contentTypes) {
                if ($contentDraftListItem->hasVersionInfo()) {
                    $versionInfo = $contentDraftListItem->getVersionInfo();
                    $contentType = $contentTypes[$versionInfo->getContentInfo()->contentTypeId];

                    return $this->valueFactory->createContentDraft($contentDraftListItem, $contentType);
                }

                return $this->valueFactory->createUnauthorizedContentDraft($contentDraftListItem);
            },
            $contentDraftListItems
        );

        return $this;
    }

    /**
     * @return ContentDraftInterface[]
     */
    public function getContentDrafts(): array
    {
        return $this->data;
    }
}

class_alias(ContentDraftListDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\ContentDraftListDataset');
