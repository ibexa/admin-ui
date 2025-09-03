<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values;

use Iterator;
use IteratorAggregate;

/**
 * @implements \IteratorAggregate<\Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbarItem>
 */
final readonly class FieldTypeToolbar implements IteratorAggregate
{
    /**
     * @param \Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbarItem[] $items
     */
    public function __construct(private array $items)
    {
    }

    /**
     * @return \Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbarItem[]
     */
    public function getItems(): iterable
    {
        return $this->items;
    }

    /**
     * @return \Iterator<\Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values\FieldTypeToolbarItem>
     */
    public function getIterator(): Iterator
    {
        yield from $this->items;
    }
}
