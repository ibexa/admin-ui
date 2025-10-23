<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UI\Value\Content\RelationInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
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
    ) {}

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function getNbResults(): int
    {
        /** @phpstan-var int<0, max> */
        return $this->contentService->countRelations(
            $this->content->getVersionInfo()
        );
    }

    /**
     * @return RelationInterface[]
     *
     * @throws UnauthorizedException
     */
    public function getSlice(
        int $offset,
        int $length
    ): array {
        return $this->datasetFactory
            ->relationList()
            ->load($this->content, $offset, $length)
            ->getRelations();
    }
}
