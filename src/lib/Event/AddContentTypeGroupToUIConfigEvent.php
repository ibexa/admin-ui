<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Event;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Contracts\EventDispatcher\Event;

final class AddContentTypeGroupToUIConfigEvent extends Event
{
    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup> $contentTypeGroups
     */
    public function __construct(private array $contentTypeGroups)
    {
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup>
     */
    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    public function addContentTypeGroup(
        ContentTypeGroup $contentTypeGroup
    ): void {
        $this->contentTypeGroups[] = $contentTypeGroup;
    }
}
