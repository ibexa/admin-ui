<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem;

final readonly class UnauthorizedRelation implements RelationInterface
{
    public function __construct(private UnauthorizedRelationListItem $unauthorizedRelation)
    {
    }

    public function getUnauthorizedRelation(): UnauthorizedRelationListItem
    {
        return $this->unauthorizedRelation;
    }

    public function isAccessible(): bool
    {
        return false;
    }
}
