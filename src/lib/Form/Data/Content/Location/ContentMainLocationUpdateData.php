<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Add validation.
 */
class ContentMainLocationUpdateData
{
    public function __construct(
        #[Assert\NotBlank]
        public ?ContentInfo $contentInfo = null,
        #[Assert\NotBlank]
        public ?Location $location = null
    ) {
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }
}
