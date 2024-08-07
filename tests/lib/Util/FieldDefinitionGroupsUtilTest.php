<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Util;

use Ibexa\AdminUi\Util\FieldDefinitionGroupsUtil;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;

class FieldDefinitionGroupsUtilTest extends TestCase
{
    public function testGroupFieldDefinitions()
    {
        $randomGroupFieldDefinition = new FieldDefinition(['fieldGroup' => 'random']);
        $testGroupFieldDefinition = new FieldDefinition(['fieldGroup' => 'test']);
        $defaultGroupFieldDefinition = new FieldDefinition();

        $fieldDefinitions = [
            $randomGroupFieldDefinition,
            $testGroupFieldDefinition,
            $defaultGroupFieldDefinition,
        ];
        $groupedFieldDefinitions = [
            'random' => [
                'name' => 'Random',
                'fieldDefinitions' => [$randomGroupFieldDefinition],
            ],
            'test' => [
                'name' => 'Test',
                'fieldDefinitions' => [$testGroupFieldDefinition],
            ],
            'content' => [
                'name' => 'Content',
                'fieldDefinitions' => [$defaultGroupFieldDefinition],
            ],
        ];

        $fieldsGroupsListHelper = $this->getMockBuilder(FieldsGroupsList::class)->getMock();
        $fieldsGroupsListHelper
            ->method('getDefaultGroup')
            ->willReturn('content');
        $fieldsGroupsListHelper
            ->method('getGroups')
            ->willReturn(['another' => 'Another', 'test' => 'Test', 'random' => 'Random', 'content' => 'Content']);

        $util = new FieldDefinitionGroupsUtil($fieldsGroupsListHelper);

        self::assertSame($groupedFieldDefinitions, $util->groupFieldDefinitions($fieldDefinitions));
        self::assertEquals($groupedFieldDefinitions, $util->groupFieldDefinitions($fieldDefinitions));
    }
}
