<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;

class ContentObjectStateUpdateData
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo
     */
    private $contentInfo;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
     */
    private $objectStateGroup;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState
     */
    private $objectState;

    public function __construct(
        ?ContentInfo $contentInfo = null,
        ?ObjectStateGroup $objectStateGroup = null,
        ?ObjectState $objectState = null
    ) {
        $this->contentInfo = $contentInfo;
        $this->objectStateGroup = $objectStateGroup;
        $this->objectState = $objectState;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function setObjectStateGroup(ObjectStateGroup $objectStateGroup)
    {
        $this->objectStateGroup = $objectStateGroup;
    }

    public function getObjectState(): ?ObjectState
    {
        return $this->objectState;
    }

    public function setObjectState(ObjectState $objectState)
    {
        $this->objectState = $objectState;
    }
}

class_alias(ContentObjectStateUpdateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ObjectState\ContentObjectStateUpdateData');
