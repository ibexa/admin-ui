<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

final class AdminUiFormsFieldTypesConfigurationTest extends TestCase
{
    private Processor $processor;

    protected function setUp(): void
    {
        $this->processor = new Processor();
    }

    public function testMetaAndUniqueDefaultToFalseWhenNotConfigured(): void
    {
        $result = $this->processFieldTypes([
            'plain_field' => [],
        ]);

        self::assertSame(
            ['meta' => false, 'unique' => false],
            $result['plain_field']
        );
    }

    public function testUniqueCanBeExplicitlyEnabledForAMetaFieldType(): void
    {
        $result = $this->processFieldTypes([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => true],
        ]);

        self::assertSame(
            ['meta' => true, 'position' => 1, 'unique' => true],
            $result['seo_metadata']
        );
    }

    public function testPositionIsRequiredForMetaFieldTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "position" option is required for all Meta Field Types');

        $this->processFieldTypes([
            'seo_metadata' => ['meta' => true],
        ]);
    }

    public function testPositionIsNotRequiredForNonMetaFieldTypes(): void
    {
        $result = $this->processFieldTypes([
            'plain_field' => ['meta' => false],
        ]);

        self::assertArrayNotHasKey('position', $result['plain_field']);
    }

    /**
     * @param array<string, array<string, mixed>> $fieldTypes
     *
     * @return array<string, array<string, mixed>>
     */
    private function processFieldTypes(array $fieldTypes): array
    {
        $treeBuilder = new TreeBuilder('admin_ui_forms_test');
        $nodeBuilder = $treeBuilder->getRootNode()->children();
        (new AdminUiForms())->addSemanticConfig($nodeBuilder);

        $config = $this->processor->process($treeBuilder->buildTree(), [
            [
                'admin_ui_forms' => [
                    'content_type_edit' => [
                        'field_types' => $fieldTypes,
                    ],
                ],
            ],
        ]);

        return $config['admin_ui_forms']['content_type_edit']['field_types'];
    }
}
