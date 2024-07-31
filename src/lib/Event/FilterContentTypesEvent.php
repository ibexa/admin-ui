<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @phpstan-import-type TContentTypeData from \Ibexa\AdminUi\UI\Config\Provider\ContentTypes
 */
final class FilterContentTypesEvent extends Event
{
    /** @var array<string, array<TContentTypeData>> */
    private array $contentTypeGroups;

    /**
     * @param array<string, array<TContentTypeData>> $contentTypeGroups
     */
    public function __construct(
        array $contentTypeGroups
    ) {
        $this->contentTypeGroups = $contentTypeGroups;
    }

    /**
     * @return array<string, array<TContentTypeData>>
     */
    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    public function removeContentTypeGroup(string $contentTypeGroupIdentifier): void
    {
        unset($this->contentTypeGroups[$contentTypeGroupIdentifier]);
    }
}
