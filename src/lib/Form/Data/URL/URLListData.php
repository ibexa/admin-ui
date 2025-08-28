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
    public function __construct(
        public readonly ?string $searchQuery = null,
        public readonly ?bool $status = null,
    ) {
    }
}
