<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\InlineFieldEdit;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\InlineFieldEdit
 */
final class InlineFieldEditTest extends TestCase
{
    private InlineFieldEdit $parser;

    private ContextualizerInterface&MockObject $contextualizer;

    protected function setUp(): void
    {
        $this->parser = new InlineFieldEdit();
        $this->contextualizer = $this->createMock(ContextualizerInterface::class);
    }

    /**
     * Test the default shape (as produced by the Symfony Config tree defaults) is mapped as-is.
     */
    public function testDefaultShapeIsMapped(): void
    {
        $scopeSettings = [
            'inline_field_edit' => [
                'enabled' => false,
                'supported_field_types' => [
                    'ibexa_string',
                    'ibexa_text',
                    'ibexa_email',
                    'ibexa_integer',
                    'ibexa_float',
                    'ibexa_boolean',
                    'ibexa_date',
                    'ibexa_datetime',
                    'ibexa_time',
                ],
                'excluded_field_types' => [],
            ],
        ];
        $currentScope = 'default';

        $this->contextualizer
            ->expects(self::exactly(3))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    'inline_field_edit.enabled',
                    $currentScope,
                    false,
                ],
                [
                    'inline_field_edit.supported_field_types',
                    $currentScope,
                    [
                        'ibexa_string',
                        'ibexa_text',
                        'ibexa_email',
                        'ibexa_integer',
                        'ibexa_float',
                        'ibexa_boolean',
                        'ibexa_date',
                        'ibexa_datetime',
                        'ibexa_time',
                    ],
                ],
                [
                    'inline_field_edit.excluded_field_types',
                    $currentScope,
                    [],
                ]
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * Test an overridden configuration is mapped verbatim.
     */
    public function testOverriddenConfigurationIsMapped(): void
    {
        $scopeSettings = [
            'inline_field_edit' => [
                'enabled' => true,
                'supported_field_types' => ['ibexa_string', 'ibexa_text'],
                'excluded_field_types' => ['ibexa_text'],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::exactly(3))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    'inline_field_edit.enabled',
                    $currentScope,
                    true,
                ],
                [
                    'inline_field_edit.supported_field_types',
                    $currentScope,
                    ['ibexa_string', 'ibexa_text'],
                ],
                [
                    'inline_field_edit.excluded_field_types',
                    $currentScope,
                    ['ibexa_text'],
                ]
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    public function testNothingIsMappedWhenNodeIsNotConfigured(): void
    {
        $scopeSettings = [];
        $currentScope = 'default';

        $this->contextualizer
            ->expects(self::never())
            ->method('setContextualParameter');

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
