<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\DependencyInjection\Configuration;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class IgnoredConfigParser extends AbstractParser
{
    /** @var array<string> */
    private array $keys;

    /**
     * @param array<string> $keys
     */
    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        foreach ($this->keys as $key) {
            $nodeBuilder->variableNode($key)->defaultNull()->end();
        }
    }

    /**
     * @param array<string, mixed> $scopeSettings
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
    }
}
