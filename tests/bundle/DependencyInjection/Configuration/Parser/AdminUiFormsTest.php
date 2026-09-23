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
        $matcher = self::atLeast(2);

        $this->contextualizer
            ->expects($matcher)
            ->method('setContextualParameter')->willReturnCallback(static function (...$parameters) use ($matcher, $currentScope, $expectedTemplatesList): void {
            if ($matcher->numberOfInvocations() === 1) {
                self::assertSame(AdminUiForms::FORM_TEMPLATES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame($expectedTemplatesList, $parameters[2]);
            }
            if ($matcher->numberOfInvocations() === 2) {
                self::assertSame(AdminUiForms::FIELD_TYPES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame([], $parameters[2]);
            }
        });

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
        $matcher = self::atLeast(2);

        $this->contextualizer
            ->expects($matcher)
            ->method('setContextualParameter')->willReturnCallback(static function (...$parameters) use ($matcher, $currentScope, $expectedFieldTypeSettings): void {
            if ($matcher->numberOfInvocations() === 1) {
                self::assertSame(AdminUiForms::FORM_TEMPLATES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame([], $parameters[2]);
            }
            if ($matcher->numberOfInvocations() === 2) {
                self::assertSame(AdminUiForms::FIELD_TYPES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame($expectedFieldTypeSettings, $parameters[2]);
            }
        });

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
        $matcher = self::atLeast(2);

        $this->contextualizer
            ->expects($matcher)
            ->method('setContextualParameter')->willReturnCallback(static function (...$parameters) use ($matcher, $currentScope): void {
            if ($matcher->numberOfInvocations() === 1) {
                self::assertSame(AdminUiForms::FORM_TEMPLATES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame([], $parameters[2]);
            }
            if ($matcher->numberOfInvocations() === 2) {
                self::assertSame(AdminUiForms::FIELD_TYPES_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame([], $parameters[2]);
            }
            if ($matcher->numberOfInvocations() === 3) {
                self::assertSame(AdminUiForms::META_FIELD_GROUPS_LIST_PARAM, $parameters[0]);
                self::assertSame($currentScope, $parameters[1]);
                self::assertSame(['metadata', 'seo'], $parameters[2]);
            }
        });

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
