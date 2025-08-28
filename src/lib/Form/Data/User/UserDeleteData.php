<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\User;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

final class UserDeleteData
{
    public function __construct(private ?ContentInfo $contentInfo = null)
    {
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }
}
