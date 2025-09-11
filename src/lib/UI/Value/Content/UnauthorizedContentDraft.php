<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem;

final readonly class UnauthorizedContentDraft implements ContentDraftInterface
{
    public function __construct(private UnauthorizedContentDraftListItem $unauthorizedContentDraft)
    {
    }

    public function getUnauthorizedContentDraft(): UnauthorizedContentDraftListItem
    {
        return $this->unauthorizedContentDraft;
    }

    public function isAccessible(): bool
    {
        return false;
    }
}
