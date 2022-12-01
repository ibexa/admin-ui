<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\User;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

class UserDeleteData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null */
    private $contentInfo;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     */
    public function __construct(?ContentInfo $contentInfo = null)
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     */
    public function setContentInfo(?ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }
}

class_alias(UserDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\User\UserDeleteData');
