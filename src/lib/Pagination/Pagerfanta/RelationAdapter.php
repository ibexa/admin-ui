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

final class RelationAdapter implements AdapterInterface
{
    private ContentService $contentService;

    private DatasetFactory $datasetFactory;

    private Content $content;

    public function __construct(
        ContentService $contentService,
        DatasetFactory $datasetFactory,
        Content $content
    ) {
        $this->contentService = $contentService;
        $this->datasetFactory = $datasetFactory;
        $this->content = $content;
    }

    public function getNbResults(): int
    {
        return $this->contentService->countRelations($this->content->getVersionInfo());
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\RelationInterface[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSlice($offset, $length): array
    {
        return $this->datasetFactory
            ->relationList()
            ->load($this->content, $offset, $length)
            ->getRelations();
    }
}
