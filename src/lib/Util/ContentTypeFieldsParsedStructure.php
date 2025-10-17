<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

final class ContentTypeFieldsParsedStructure
{
    /** @var non-empty-list<string>|null */
    private ?array $groups;

    /** @var non-empty-list<string>|null */
    private ?array $contentTypes;

    /** @var non-empty-list<string>|null */
    private ?array $fields;

    /**
     * @param non-empty-list<string>|null $groups
     * @param non-empty-list<string>|null $contentTypes
     * @param non-empty-list<string>|null $fields
     */
    public function __construct(
        ?array $groups,
        ?array $contentTypes,
        ?array $fields
    ) {
        $this->groups = $groups;
        $this->contentTypes = $contentTypes;
        $this->fields = $fields;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getContentTypes(): ?array
    {
        return $this->contentTypes;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function isAllChosen(): bool
    {
        return $this->groups === null && $this->contentTypes === null && $this->fields === null;
    }
}
