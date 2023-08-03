<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

final class RoleLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    private RoleService $roleService;

    public function __construct(
        RoleService $roleService
    ) {
        $this->roleService = $roleService;
    }

    protected function getSelectionChoices(): array
    {
        $choices = [];
        foreach ($this->roleService->loadRoles() as $role) {
            $choices[$role->id] = $role->identifier;
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];

        foreach ($limitation->limitationValues as $roleId) {
            $values[] = $this->roleService->loadRole((int)$roleId)->identifier;
        }

        return $values;
    }
}
