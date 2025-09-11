<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias as CoreURLAlias;

class UrlAlias extends CoreURLAlias
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly CoreURLAlias $urlAlias,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($urlAlias) + $properties);
    }
}
