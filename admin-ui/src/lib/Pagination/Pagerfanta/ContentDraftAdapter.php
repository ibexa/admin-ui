<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\AdminUi\UI\Value\Content\ContentDraftInterface>
 */
final class ContentDraftAdapter implements AdapterInterface
{
    private ContentService $contentService;

    private DatasetFactory $datasetFactory;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\UI\Dataset\DatasetFactory $datasetFactory
     */
    public function __construct(ContentService $contentService, DatasetFactory $datasetFactory)
    {
        $this->contentService = $contentService;
        $this->datasetFactory = $datasetFactory;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getNbResults(): int
    {
        /** @var int<0, max> */
        return $this->contentService->countContentDrafts();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->datasetFactory
            ->contentDraftList()
            ->load(null, $offset, $length)
            ->getContentDrafts();
    }
}
