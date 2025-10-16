<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause\Identifier;
use Pagerfanta\Adapter\AdapterInterface;

final class ContentTypeListAdapter implements AdapterInterface
{
    private ContentTypeService $contentTypeService;

    private ContentTypeQuery $query;

    /** @var list<string> */
    private array $languages;

    /**
     * @param list<string> $languages
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        array $languages,
        ?ContentTypeQuery $query = null
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->languages = $languages;
        $this->query = $query ?? new ContentTypeQuery(null, [new Identifier()]);
    }

    public function getNbResults(): int
    {
        $query = clone $this->query;
        $query->setLimit(0);

        return $this->contentTypeService->findContentTypes($query, $this->languages)->getTotalCount();
    }

    public function getSlice($offset, $length): iterable
    {
        $query = clone $this->query;
        $query->setOffset($offset);
        $query->setLimit($length);

        return $this->contentTypeService->findContentTypes($query, $this->languages)->getIterator();
    }
}
