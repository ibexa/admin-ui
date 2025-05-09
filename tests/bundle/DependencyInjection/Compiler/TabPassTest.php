<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\Tab\TabRegistry;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\TabPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TabPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDefinition(TabRegistry::class, new Definition());
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TabPass());
    }

    public function testProcess(): void
    {
        $taggedServiceId = 'collected_service';
        $collectedService = new Definition();
        $collectedService->addTag(TabPass::TAG_TAB, ['group' => 'someGroup']);
        $this->setDefinition($taggedServiceId, $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $taggedServiceId,
            TabPass::TAG_TAB,
            ['group' => 'someGroup']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            TabRegistry::class,
            'addTab',
            [new Reference($taggedServiceId), 'someGroup']
        );
    }
}
