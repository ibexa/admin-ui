<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\FieldTypeToolbar\Values;

final readonly class FieldTypeToolbarItem
{
    public function __construct(
        private string $identifier,
        private string $name,
        private bool $isSingular = false
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSingular(): bool
    {
        return $this->isSingular;
    }
}
