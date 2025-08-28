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

final class ContentObjectStateUpdateData
{
    public function __construct(
        private ?ContentInfo $contentInfo = null,
        private ?ObjectStateGroup $objectStateGroup = null,
        private ?ObjectState $objectState = null
    ) {
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    public function getObjectStateGroup(): ?ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function setObjectStateGroup(?ObjectStateGroup $objectStateGroup): void
    {
        $this->objectStateGroup = $objectStateGroup;
    }

    public function getObjectState(): ?ObjectState
    {
        return $this->objectState;
    }

    public function setObjectState(?ObjectState $objectState): void
    {
        $this->objectState = $objectState;
    }
}
