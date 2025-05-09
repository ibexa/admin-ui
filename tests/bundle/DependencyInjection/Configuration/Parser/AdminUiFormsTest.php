<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test AdminUiForms SiteAccess-aware Configuration Parser.
 */
class AdminUiFormsTest extends TestCase
{
    private AdminUiForms $parser;

    private ContextualizerInterface&MockObject $contextualizer;

    protected function setUp(): void
    {
        $this->parser = new AdminUiForms();
        $this->contextualizer = $this->createMock(ContextualizerInterface::class);
    }

    /**
     * Test given Content edit form templates are sorted according to their priority when mapping.
     */
    public function testContentEditFormTemplatesAreMapped(): void
    {
        $scopeSettings = [
            'admin_ui_forms' => [
                'content_edit' => [
                    'form_templates' => [
                        ['template' => 'my_template-01.html.twig', 'priority' => 1],
                        ['template' => 'my_template-02.html.twig', 'priority' => 0],
                        ['template' => 'my_template-03.html.twig', 'priority' => 2],
                    ],
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $expectedTemplatesList = [
            'my_template-03.html.twig',
            'my_template-01.html.twig',
            'my_template-02.html.twig',
        ];

        $this->contextualizer
            ->expects(self::atLeast(2))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    AdminUiForms::FORM_TEMPLATES_PARAM,
                    $currentScope,
                    $expectedTemplatesList,
                ],
                [
                    AdminUiForms::FIELD_TYPES_PARAM,
                    $currentScope,
                    [],
                ],
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * Test given fieldtype settings are mapped.
     */
    public function testContentEditFieldTypesAreMapped(): void
    {
        $scopeSettings = [
            'admin_ui_forms' => [
                'content_edit' => [
                    'fieldtypes' => [
                        'my_fieldtype' => ['meta' => true],
                        'my_fieldtype_2' => ['meta' => false],
                    ],
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $expectedFieldTypeSettings = [
            'my_fieldtype' => ['meta' => true],
            'my_fieldtype_2' => ['meta' => false],
        ];

        $this->contextualizer
            ->expects(self::atLeast(2))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    AdminUiForms::FORM_TEMPLATES_PARAM,
                    $currentScope,
                    [],
                ],
                [
                    AdminUiForms::FIELD_TYPES_PARAM,
                    $currentScope,
                    $expectedFieldTypeSettings,
                ],
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * Test 'meta_field_groups_list' fieldtype settings are mapped.
     */
    public function testContentEditMetaFieldgroupListIsMapped(): void
    {
        $scopeSettings = [
            'admin_ui_forms' => [
                'content_edit' => [
                    'meta_field_groups_list' => [
                        'metadata',
                        'seo',
                    ],
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::atLeast(2))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    AdminUiForms::FORM_TEMPLATES_PARAM,
                    $currentScope,
                    [],
                ],
                [
                    AdminUiForms::FIELD_TYPES_PARAM,
                    $currentScope,
                    [],
                ],
                [
                    AdminUiForms::META_FIELD_GROUPS_LIST_PARAM,
                    $currentScope,
                    ['metadata', 'seo'],
                ],
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
