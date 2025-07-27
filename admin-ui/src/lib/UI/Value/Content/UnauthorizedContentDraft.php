<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem;

class UnauthorizedContentDraft implements ContentDraftInterface
{
    private UnauthorizedContentDraftListItem $unauthorizedContentDraft;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem $unauthorizedContentDraft
     */
    public function __construct(UnauthorizedContentDraftListItem $unauthorizedContentDraft)
    {
        $this->unauthorizedContentDraft = $unauthorizedContentDraft;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem
     */
    public function getUnauthorizedContentDraft(): UnauthorizedContentDraftListItem
    {
        return $this->unauthorizedContentDraft;
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return false;
    }
}
