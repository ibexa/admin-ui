<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

final readonly class VersionId
{
    public function __construct(
        private int $contentId,
        private int $versionNo
    ) {
    }

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function getVersionNo(): int
    {
        return $this->versionNo;
    }

    public function __toString(): string
    {
        return implode(':', [
            $this->contentId,
            $this->versionNo,
        ]);
    }

    public static function fromString(string $id): self
    {
        list($contentId, $versionNo) = explode(':', $id);

        return new self((int) $contentId, (int) $versionNo);
    }
}
