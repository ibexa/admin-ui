<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URL;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

final class URLListData extends ValueObject
{
    public ?string $searchQuery;

    public ?bool $status;

    public function __construct(
        ?string $searchQuery = null,
        ?bool $status = null,
    ) {
        $this->searchQuery = $searchQuery;
        $this->status = $status;
    }
}
