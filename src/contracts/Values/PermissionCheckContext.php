<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

final class PermissionCheckContext
{
    private ValueObject $subject;

    /** @var array<\Ibexa\Contracts\Core\Repository\Values\ValueObject> */
    private array $targets;

    private ?CriterionInterface $criteria;

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ValueObject> $targets
     */
    public function __construct(
        ValueObject $subject,
        array $targets,
        ?CriterionInterface $criteria = null
    ) {
        $this->subject = $subject;
        $this->targets = $targets;
        $this->criteria = $criteria;
    }

    public function getSubject(): ValueObject
    {
        return $this->subject;
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\ValueObject>
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    public function getCriteria(): ?CriterionInterface
    {
        return $this->criteria;
    }
}
