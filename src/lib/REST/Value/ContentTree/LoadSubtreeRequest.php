<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Ibexa\Rest\Value as RestValue;

class LoadSubtreeRequest extends RestValue
{
    /** @var \Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode[] */
    public $nodes;

    public ?Filter $filter;

    /**
     * @param array $nodes
     */
    public function __construct(array $nodes = [], Filter $filter = null)
    {
        $this->nodes = $nodes;
        $this->filter = $filter;
    }
}

class_alias(LoadSubtreeRequest::class, 'EzSystems\EzPlatformAdminUi\REST\Value\ContentTree\LoadSubtreeRequest');
