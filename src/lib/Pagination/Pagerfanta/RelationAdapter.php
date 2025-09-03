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

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\AdminUi\UI\Value\Content\RelationInterface>
 */
final readonly class RelationAdapter implements AdapterInterface
{
    public function __construct(
        private ContentService $contentService,
        private DatasetFactory $datasetFactory,
        private Content $content
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getNbResults(): int
    {
        /** @phpstan-var int<0, max> */
        return $this->contentService->countRelations(
            $this->content->getVersionInfo()
        );
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\RelationInterface[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSlice(int $offset, int $length): array
    {
        return $this->datasetFactory
            ->relationList()
            ->load($this->content, $offset, $length)
            ->getRelations();
    }
}
