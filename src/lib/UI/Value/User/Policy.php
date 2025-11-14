<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\User;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Policy as APIPolicy;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

class Policy extends APIPolicy
{
    /**
     * Limitations assigned to this policy.
     *
     * @var Limitation[]
     */
    protected $limitations = [];

    /**
     * RoleAssignment to which policy belongs.
     *
     * @var RoleAssignment
     */
    protected $role_assignment;

    /**
     * @param APIPolicy $policy
     * @param array $properties
     */
    public function __construct(
        APIPolicy $policy,
        array $properties = []
    ) {
        parent::__construct(get_object_vars($policy) + $properties);

        $this->limitations = $policy->getLimitations();
    }

    /**
     * @return Limitation[]
     */
    public function getLimitations(): iterable
    {
        return $this->limitations;
    }
}

class_alias(Policy::class, 'EzSystems\EzPlatformAdminUi\UI\Value\User\Policy');
