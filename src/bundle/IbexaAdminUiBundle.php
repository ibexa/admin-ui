<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi;

use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\ComponentPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\LimitationFormMapperPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\LimitationValueMapperPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\TabPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\UiConfigProviderPass;
use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaAdminUiBundle extends Bundle
{
    public const ADMIN_GROUP_NAME = 'admin_group';

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function build(ContainerBuilder $container)
    {
        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $core */
        $core = $container->getExtension('ibexa');

        $configParsers = $this->getConfigParsers();
        array_walk($configParsers, [$core, 'addConfigParser']);

        $core->addDefaultSettings(__DIR__ . '/Resources/config', ['ezplatform_default_settings.yaml']);

        $this->addCompilerPasses($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function addCompilerPasses(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TabPass());
        $container->addCompilerPass(new UiConfigProviderPass());
        $container->addCompilerPass(new ComponentPass());
        $container->addCompilerPass(new LimitationFormMapperPass());
        $container->addCompilerPass(new LimitationValueMapperPass());
        $container->addCompilerPass(new FieldTypeFormMapperDispatcherPass());
    }

    /**
     * @return \Ibexa\Bundle\Core\DependencyInjection\Configuration\ParserInterface[]
     */
    private function getConfigParsers(): array
    {
        return [
            new Parser\LocationIds(),
            new Parser\Module\Subitems(),
            new Parser\Module\UniversalDiscoveryWidget(),
            new Parser\Module\ContentTree(),
            new Parser\Pagination(),
            new Parser\Security(),
            new Parser\UserGroupIdentifier(),
            new Parser\SubtreeOperations(),
            new Parser\Notifications(),
            new Parser\ContentTranslateView(),
            new Parser\AdminUiForms(),
            new Parser\ContentType(),
            new Parser\ContentTypeGroup(),
            new Parser\SubtreePath(),
            new Parser\LimitationValueTemplates(),
            new Parser\Assets(),
            new Parser\AdminUiParser(),
            new Parser\UserProfile(),
        ];
    }
}

class_alias(IbexaAdminUiBundle::class, 'EzSystems\EzPlatformAdminUiBundle\EzPlatformAdminUiBundle');
