<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Form\Data;

use Ibexa\AdminUi\Form\Data\DateRangeData;

final class SearchQueryData
{
    private array $statuses = [];
    private ?string $type = null;

    private ?DateRangeData $createdRange = null;


    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): void
    {
        $this->statuses = $statuses;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getCreatedRange(): ?DateRangeData
    {
        return $this->createdRange;
    }

    public function setCreatedRange(?DateRangeData $createdRange): void
    {
        $this->createdRange = $createdRange;
    }
}
