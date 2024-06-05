<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Pagerfanta\Adapter\AdapterInterface;

final class ReverseRelationAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\AdminUi\UI\Dataset\DatasetFactory */
    private $datasetFactory;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    private $content;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\UI\Dataset\DatasetFactory $datasetFactory
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     */
    public function __construct(
        ContentService $contentService,
        DatasetFactory $datasetFactory,
        Content $content
    ) {
        $this->contentService = $contentService;
        $this->datasetFactory = $datasetFactory;
        $this->content = $content;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        return $this->contentService->countReverseRelations($this->content->contentInfo);
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        return $this->datasetFactory
            ->reverseRelationList()
            ->load($this->content, $offset, $length)
            ->getReverseRelations();
    }
}
