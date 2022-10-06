<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems\Values;

use Ibexa\Rest\Value as RestValue;

class ContentTypeRemoteIdList extends RestValue
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] */
    public $contentTypes;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] $contentTypes
     */
    public function __construct(array $contentTypes)
    {
        $this->contentTypes = $contentTypes;
    }
}

class_alias(ContentTypeRemoteIdList::class, 'EzSystems\EzPlatformAdminUi\UI\Module\Subitems\Values\ContentTypeRemoteIdList');
