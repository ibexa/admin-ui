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
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class RoleLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
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

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('role'),
                'ezplatform_content_forms_policies'
            )->setDesc('Role'),
        ];
    }
}
