<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ContentTypeGroup;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;

final class ContentTypeGroupUpdateData
{
    private ?ContentTypeGroup $contentTypeGroup;

    private string $identifier;

    public function __construct(?ContentTypeGroup $contentTypeGroup = null)
    {
        if ($contentTypeGroup instanceof ContentTypeGroup) {
            $this->contentTypeGroup = $contentTypeGroup;
            $this->identifier = $contentTypeGroup->identifier;
        }
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }
}
