<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;

class FieldDefinitionGroupsUtil
{
    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsListHelper;

    /**
     * @param \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList $fieldsGroupsListHelper
     */
    public function __construct(FieldsGroupsList $fieldsGroupsListHelper)
    {
        $this->fieldsGroupsListHelper = $fieldsGroupsListHelper;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    public function groupFieldDefinitions(iterable $fieldDefinitions): array
    {
        $fieldsGroups = $this->fieldsGroupsListHelper->getGroups();

        $fieldDefinitionsByGroup = [];
        foreach ($fieldDefinitions as $fieldDefinition) {
            $groupId = $fieldDefinition->fieldGroup;
            if ($groupId === null) {
                $groupId = $this->fieldsGroupsListHelper->getDefaultGroup();
            }

            $fieldDefinitionsByGroup[$groupId]['name'] = $fieldDefinitionsByGroup[$groupId]['name']
                ?? $fieldsGroups[$groupId]
                ?? $groupId;
            $fieldDefinitionsByGroup[$groupId]['fieldDefinitions'][] = $fieldDefinition;
        }

        return $fieldDefinitionsByGroup;
    }
}

class_alias(FieldDefinitionGroupsUtil::class, 'EzSystems\EzPlatformAdminUi\Util\FieldDefinitionGroupsUtil');
