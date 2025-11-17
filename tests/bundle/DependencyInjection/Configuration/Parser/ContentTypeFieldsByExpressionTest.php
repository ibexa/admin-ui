<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\ContentTypeFieldsByExpression;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\SubtreeOperations
 */
final class ContentTypeFieldsByExpressionTest extends TestCase
{
    private ContentTypeFieldsByExpression $parser;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface */
    private ContextualizerInterface $contextualizer;

    protected function setUp(): void
    {
        $this->parser = new ContentTypeFieldsByExpression();
        $this->contextualizer = $this->createMock(ContextualizerInterface::class);
    }

    public function testMapConfiguration(): void
    {
        $scopeSettings = [
            'content_type_fields_by_expression' => [
                'configurations' => [
                    'vectorizable_fields' => ['ezstring', 'eztext'],
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::once())
            ->method('setContextualParameter')
            ->with(
                'content_type_fields_by_expression.configurations.vectorizable_fields',
                $currentScope,
                ['ezstring', 'eztext'],
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    public function testMapEmptyConfiguration(): void
    {
        $scopeSettings = [
            'content_type_fields_by_expression' => [
                'configurations' => [],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::never())
            ->method('setContextualParameter');

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
