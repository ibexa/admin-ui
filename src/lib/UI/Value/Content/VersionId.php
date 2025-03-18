<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

class VersionId
{
    private int $contentId;

    private int $versionNo;

    /**
     * @param int $contentId
     * @param int $versionNo
     */
    public function __construct(int $contentId, int $versionNo)
    {
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
    }

    /**
     * @return int
     */
    public function getContentId(): int
    {
        return $this->contentId;
    }

    /**
     * @return int
     */
    public function getVersionNo(): int
    {
        return $this->versionNo;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(':', [
            $this->contentId,
            $this->versionNo,
        ]);
    }

    /**
     * @param string $id
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\VersionId
     */
    public static function fromString(string $id): self
    {
        list($contentId, $versionNo) = explode(':', $id);

        return new self((int) $contentId, (int) $versionNo);
    }
}
