<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URLWildcard;

final class URLWildcardListData
{
    public ?string $searchQuery;

    public ?bool $type;

    public int $limit;

    public function setSearchQuery(?string $searchQuery): void
    {
        $this->searchQuery = $searchQuery;
    }

    public function setType(?bool $type): void
    {
        $this->type = $type;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
