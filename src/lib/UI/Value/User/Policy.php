<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\User;

use Ibexa\Contracts\Core\Repository\Values\User\Policy as  APIPolicy;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

class Policy extends APIPolicy
{
    /**
     * Limitations assigned to this policy.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation[]
     */
    protected iterable $limitations;

    protected RoleAssignment $roleAssignment;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly APIPolicy $policy,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($policy) + $properties);

        $this->limitations = $policy->getLimitations();
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Limitation[]
     */
    public function getLimitations(): iterable
    {
        return $this->limitations;
    }
}
