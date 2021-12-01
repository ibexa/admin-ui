<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem;

final class UnauthorizedRelation implements RelationInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem */
    private $unauthorizedRelation;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem $unauthorizedContentDraft
     */
    public function __construct(UnauthorizedRelationListItem $unauthorizedRelation)
    {
        $this->unauthorizedRelation = $unauthorizedRelation;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem
     */
    public function getUnauthorizedRelation(): UnauthorizedRelationListItem
    {
        return $this->unauthorizedRelation;
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return false;
    }
}

class_alias(UnauthorizedRelation::class, 'EzSystems\EzPlatformAdminUi\UI\Value\Content\UnauthorizedRelation');
