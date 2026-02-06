<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\SubtreeOperations;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\SubtreeOperations
 */
final class SubtreeOperationsTest extends TestCase
{
    private SubtreeOperations $parser;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface */
    private ContextualizerInterface $contextualizer;

    /**
     * @return iterable<string, array{int}>
     */
    public function getExpectedCopySubtreeLimit(): iterable
    {
        yield 'default = 100' => [100];
        yield 'no limit = -1' => [-1];
        yield 'disabled = 0' => [0];
    }

    /**
     * @return iterable<string, array{int|null}>
     */
    public function getExpectedQuerySubtreeLimit(): iterable
    {
        yield 'no limit = -1' => [-1];
        yield 'custom limit = 1000' => [1000];
        yield 'disabled = 0' => [0];
    }

    protected function setUp(): void
    {
        $this->parser = new SubtreeOperations();
        $this->contextualizer = $this->createMock(ContextualizerInterface::class);
    }

    /**
     * @dataProvider getExpectedCopySubtreeLimit
     */
    public function testCopySubtreeLimit(int $expectedCopySubtreeLimit): void
    {
        $scopeSettings = [
            'subtree_operations' => [
                'copy_subtree' => [
                    'limit' => $expectedCopySubtreeLimit,
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::once())
            ->method('setContextualParameter')
            ->with(
                'subtree_operations.copy_subtree.limit',
                $currentScope,
                $expectedCopySubtreeLimit
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    public function testCopySubtreeLimitNotSet(): void
    {
        $scopeSettings = [
            'subtree_operations' => null,
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::never())
            ->method('setContextualParameter');

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    /**
     * @dataProvider getExpectedQuerySubtreeLimit
     */
    public function testQuerySubtreeLimit(int $expectedQuerySubtreeLimit): void
    {
        $scopeSettings = [
            'subtree_operations' => [
                'query_subtree' => [
                    'limit' => $expectedQuerySubtreeLimit,
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::once())
            ->method('setContextualParameter')
            ->with(
                'subtree_operations.query_subtree.limit',
                $currentScope,
                $expectedQuerySubtreeLimit
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    public function testQuerySubtreeLimitNotSet(): void
    {
        $scopeSettings = [
            'subtree_operations' => [
                'query_subtree' => null,
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::never())
            ->method('setContextualParameter');

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }

    public function testBothSubtreeOperationsSet(): void
    {
        $scopeSettings = [
            'subtree_operations' => [
                'copy_subtree' => [
                    'limit' => 200,
                ],
                'query_subtree' => [
                    'limit' => 500,
                ],
            ],
        ];
        $currentScope = 'admin_group';

        $this->contextualizer
            ->expects(self::exactly(2))
            ->method('setContextualParameter')
            ->withConsecutive(
                [
                    'subtree_operations.copy_subtree.limit',
                    $currentScope,
                    200,
                ],
                [
                    'subtree_operations.query_subtree.limit',
                    $currentScope,
                    500,
                ]
            );

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
