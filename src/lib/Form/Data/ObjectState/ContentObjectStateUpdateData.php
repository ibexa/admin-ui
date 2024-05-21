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

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup|null $objectStateGroup
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState|null $objectState
     */
    public function __construct(
        ContentInfo $contentInfo = null,
        ObjectStateGroup $objectStateGroup = null,
        ObjectState $objectState = null
    ) {
        $this->contentInfo = $contentInfo;
        $this->objectStateGroup = $objectStateGroup;
        $this->objectState = $objectState;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function setContentInfo(ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function setObjectStateGroup(ObjectStateGroup $objectStateGroup)
    {
        $this->objectStateGroup = $objectStateGroup;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState|null
     */
    public function getObjectState(): ?ObjectState
    {
        return $this->objectState;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function setObjectState(ObjectState $objectState)
    {
        $this->objectState = $objectState;
    }
}
