<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ContentSpecification
{
    /**
     * Check to see if the specification is satisfied.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    public function isSatisfiedBy(Content $content): bool;
}
