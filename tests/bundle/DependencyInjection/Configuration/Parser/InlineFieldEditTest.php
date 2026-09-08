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
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

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

    /**
     * Regression test for the `addDefaultsIfNotSet()` defect: a scope (e.g. a siteaccess) that never
     * mentions `inline_field_edit` at all must not have the node materialised by the Symfony Config
     * tree with its defaults. If it were, every such scope would end up with a siteaccess-scoped
     * `inline_field_edit.enabled: false` parameter, which (because ConfigResolver resolves siteaccess
     * scope before siteaccess-group scope) would silently override an explicit `enabled: true` set at
     * group scope.
     *
     * This exercises the actual `addSemanticConfig()` tree definition (not a hand-built
     * `$scopeSettings` array), because that is where the real defect lives.
     */
    public function testUndeclaredScopeDoesNotMaterializeTheNode(): void
    {
        $processedScopeSettings = $this->processScopeConfig([]);

        self::assertArrayNotHasKey('inline_field_edit', $processedScopeSettings);
    }

    /**
     * Chains the tree-processed result of an undeclared scope into mapConfig(), confirming that the
     * empty() guard trips and no siteaccess-scoped contextual parameter is written for a scope that
     * never declared the node. This is the closest thing to asserting the group-vs-siteaccess
     * precedence at this level: mapConfig() has no notion of ConfigResolver scope precedence, so this
     * test can only confirm the unset scope contributes nothing, which is exactly what allows the
     * group-scoped value to survive unchallenged.
     */
    public function testNoContextualParameterIsSetForScopeThatNeverDeclaresTheNode(): void
    {
        $scopeSettings = $this->processScopeConfig([]);
        $currentScope = 'admin_en';

        $this->contextualizer
            ->expects(self::never())
            ->method('setContextualParameter');

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * An explicit `enabled: false` still produces a non-empty `inline_field_edit` array (it has all
     * three children filled in via per-child defaults), so the empty() guard in mapConfig() must not
     * swallow it: the explicit false has to be written just like an explicit true would be.
     */
    public function testExplicitlyDisabledScopeIsStillMapped(): void
    {
        $processedScopeSettings = $this->processScopeConfig([
            'inline_field_edit' => [
                'enabled' => false,
            ],
        ]);
        $currentScope = 'admin_en';

        self::assertArrayHasKey('inline_field_edit', $processedScopeSettings);

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

        $this->parser->mapConfig($processedScopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * A scope declaring only `enabled: true` must still receive sensible per-child defaults for
     * `supported_field_types` and `excluded_field_types`, sourced from the semantic config tree
     * (not from the `default` scope settings in ezplatform_default_settings.yaml).
     */
    public function testPartialConfigurationIsCompletedWithChildDefaults(): void
    {
        $processedScopeSettings = $this->processScopeConfig([
            'inline_field_edit' => [
                'enabled' => true,
            ],
        ]);

        self::assertSame(
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
            $processedScopeSettings['inline_field_edit']['supported_field_types']
        );
        self::assertSame([], $processedScopeSettings['inline_field_edit']['excluded_field_types']);
    }

    /**
     * Builds and processes the real semantic config tree exposed by
     * InlineFieldEdit::addSemanticConfig(), the same way Symfony processes it for each
     * `ibexa.system.*` scope in a real installation. This is what actually reproduces (or, once
     * fixed, disproves) the `addDefaultsIfNotSet()` defect, since that defect lives in the tree
     * definition itself and cannot be observed by calling mapConfig() with a hand-built array.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function processScopeConfig(array $config): array
    {
        $treeBuilder = new TreeBuilder('system');
        $rootNode = $treeBuilder->getRootNode();
        $nodeBuilder = $rootNode->children();
        $this->parser->addSemanticConfig($nodeBuilder);
        $nodeBuilder->end();

        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), [$config]);
    }
}
