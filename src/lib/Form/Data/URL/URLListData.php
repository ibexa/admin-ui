<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URL;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

class URLListData extends ValueObject
{
    /** @var string|null */
    public $searchQuery;

    /** @var bool|null */
    public $status;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;
}
