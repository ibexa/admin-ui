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

#[\PHPUnit\Framework\Attributes\CoversClass(\Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\SubtreeOperations::class)]
final class SubtreeOperationsTest extends TestCase
{
    private SubtreeOperations $parser;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface */
    private ContextualizerInterface $contextualizer;

    /**
     * @return iterable<string, array{int}>
     */
    public static function getExpectedCopySubtreeLimit(): iterable
    {
        yield 'default = 100' => [100];
        yield 'no limit = -1' => [-1];
        yield 'disabled = 0' => [0];
    }

    /**
     * @return iterable<string, array{int|null}>
     */
    public static function getExpectedQuerySubtreeLimit(): iterable
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

    #[\PHPUnit\Framework\Attributes\DataProvider('getExpectedCopySubtreeLimit')]
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

    #[\PHPUnit\Framework\Attributes\DataProvider('getExpectedQuerySubtreeLimit')]
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
        $matcher = self::exactly(2);

        $this->contextualizer
            ->expects($matcher)
            ->method('setContextualParameter')->willReturnCallback(function (...$parameters) use ($matcher, $currentScope): void {
            if ($matcher->numberOfInvocations() === 1) {
                $this->assertSame('subtree_operations.copy_subtree.limit', $parameters[0]);
                $this->assertSame($currentScope, $parameters[1]);
                $this->assertSame(200, $parameters[2]);
            }
            if ($matcher->numberOfInvocations() === 2) {
                $this->assertSame('subtree_operations.query_subtree.limit', $parameters[0]);
                $this->assertSame($currentScope, $parameters[1]);
                $this->assertSame(500, $parameters[2]);
            }
        });

        $this->parser->mapConfig($scopeSettings, $currentScope, $this->contextualizer);
    }
}
