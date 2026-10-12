<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentTypeGroup;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeGroupDeleteData
{
    /** @var ContentTypeGroup */
    private $contentTypeGroup;

    public function __construct(?ContentTypeGroup $contentTypeGroup = null)
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    /**
     * @return ContentTypeGroup
     */
    public function getContentTypeGroup(): ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    /**
     * @param ContentTypeGroup $contentTypeGroup
     */
    public function setContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }
}

class_alias(ContentTypeGroupDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupDeleteData');
