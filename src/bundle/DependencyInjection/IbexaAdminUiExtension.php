<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\DependencyInjection;

use Ibexa\Contracts\Core\Container\Encore\ConfigurationDumper as IbexaEncoreConfigurationDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class IbexaAdminUiExtension extends Extension implements PrependExtensionInterface
{
    private const WEBPACK_CONFIG_NAMES = [
        'ibexa.config.js' => [
            'ibexa.config.js' => [],
        ],
        'ibexa.config.manager.js' => [
            'ibexa.config.manager.js' => [],
        ],
        'ibexa.webpack.custom.config.js' => [
            'ibexa.webpack.custom.config.js' => [],
        ],
        'ibexa.config.setup.js' => [
            'ibexa.config.setup.js' => [],
        ],
    ];

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('default_parameters.yaml');
        $loader->load('services.yaml');
        $loader->load('role.yaml');

        $shouldLoadTestServices = $this->shouldLoadTestServices($container);
        if ($shouldLoadTestServices) {
            $loader->load('services/test/feature_contexts.yaml');
            $loader->load('services/test/pages.yaml');
            $loader->load('services/test/components.yaml');
        }

        (new IbexaEncoreConfigurationDumper($container))->dumpCustomConfiguration(
            self::WEBPACK_CONFIG_NAMES
        );
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container): void
    {
        $this->prependViews($container);
        $this->prependImageVariations($container);
        $this->prependUniversalDiscoveryWidget($container);
        $this->prependEzDesignConfiguration($container);
        $this->prependAdminUiFormsConfiguration($container);
        $this->prependBazingaJsTranslationConfiguration($container);
        $this->prependJMSTranslation($container);
    }

    private function prependViews(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/views.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($configFile));
    }

    private function prependImageVariations(ContainerBuilder $container): void
    {
        $imageConfigFile = __DIR__ . '/../Resources/config/image_variations.yaml';
        $config = Yaml::parse(file_get_contents($imageConfigFile));
        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($imageConfigFile));
    }

    private function prependUniversalDiscoveryWidget(ContainerBuilder $container): void
    {
        $udwConfigFile = __DIR__ . '/../Resources/config/universal_discovery_widget.yaml';
        $config = Yaml::parse(file_get_contents($udwConfigFile));
        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($udwConfigFile));
    }

    private function prependEzDesignConfiguration(ContainerBuilder $container): void
    {
        $eZDesignConfigFile = __DIR__ . '/../Resources/config/ezdesign.yaml';
        $config = Yaml::parseFile($eZDesignConfigFile);
        $container->prependExtensionConfig('ibexa_design_engine', $config['ibexa_design_engine']);
        $container->prependExtensionConfig('ibexa', $config['ibexa']);
        $container->addResource(new FileResource($eZDesignConfigFile));
    }

    private function prependAdminUiFormsConfiguration(ContainerBuilder $container): void
    {
        $adminUiFormsConfigFile = __DIR__ . '/../Resources/config/admin_ui_forms.yaml';
        $config = Yaml::parseFile($adminUiFormsConfigFile);
        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($adminUiFormsConfigFile));
    }

    private function prependBazingaJsTranslationConfiguration(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/bazinga_js_translation.yaml';
        $config = Yaml::parseFile($configFile);
        $container->prependExtensionConfig('bazinga_js_translation', $config);
        $container->addResource(new FileResource($configFile));
    }

    private function prependJMSTranslation(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ibexa_admin_ui' => [
                    'dirs' => [
                        __DIR__ . '/../../../src/',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                    'excluded_dirs' => ['Behat', 'Tests', 'node_modules'],
                    'extractors' => ['ez_location_sorting'],
                ],
            ],
        ]);
    }

    private function shouldLoadTestServices(ContainerBuilder $container): bool
    {
        return $container->hasParameter('ibexa.behat.browser.enabled')
            && true === $container->getParameter('ibexa.behat.browser.enabled');
    }
}
