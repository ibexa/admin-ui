<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class IbexaAdminUiExtension extends Extension implements PrependExtensionInterface
{
    private const WEBPACK_CONFIG_NAMES = [
        'ez.config.js',
        'ez.config.manager.js',
        'ez.webpack.custom.config.js',
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
    public function load(array $configs, ContainerBuilder $container)
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
        
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        $rootPath = $container->getParameter('kernel.project_dir') . '/';
        $targetPath = 'var/encore';

        foreach (self::WEBPACK_CONFIG_NAMES as $configName) {
            $this->dumpConfigurationPathsToFile($configName, $rootPath, $targetPath, $bundlesMetadata);
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
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
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }

    private function prependImageVariations(ContainerBuilder $container)
    {
        $imageConfigFile = __DIR__ . '/../Resources/config/image_variations.yaml';
        $config = Yaml::parse(file_get_contents($imageConfigFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($imageConfigFile));
    }

    private function prependUniversalDiscoveryWidget(ContainerBuilder $container)
    {
        $udwConfigFile = __DIR__ . '/../Resources/config/universal_discovery_widget.yaml';
        $config = Yaml::parse(file_get_contents($udwConfigFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($udwConfigFile));
    }

    private function prependEzDesignConfiguration(ContainerBuilder $container)
    {
        $eZDesignConfigFile = __DIR__ . '/../Resources/config/ezdesign.yaml';
        $config = Yaml::parseFile($eZDesignConfigFile);
        $container->prependExtensionConfig('ezdesign', $config['ezdesign']);
        $container->prependExtensionConfig('ezpublish', $config['ezpublish']);
        $container->addResource(new FileResource($eZDesignConfigFile));
    }

    private function prependAdminUiFormsConfiguration(ContainerBuilder $container)
    {
        $adminUiFormsConfigFile = __DIR__ . '/../Resources/config/admin_ui_forms.yaml';
        $config = Yaml::parseFile($adminUiFormsConfigFile);
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($adminUiFormsConfigFile));
    }

    private function prependBazingaJsTranslationConfiguration(ContainerBuilder $container)
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
                'ezplatform_admin_ui' => [
                    'dirs' => [
                        __DIR__ . '/../../../src/',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                    'excluded_dirs' => ['Behat', 'Tests', 'node_modules'],
                    'extractors' => ['ez_policy', 'ez_policy_limitation', 'ez_location_sorting'],
                ],
            ],
        ]);
    }
    
    /**
     * Looks for Resources/encore/ files in every registered and enabled bundle.
     * Dumps json list of paths to files it finds.
     *
     * @param string $targetPath Where to put eZ Encore paths configuration file (default: var/encore)
     */
    private function dumpConfigurationPathsToFile(string $configName, string $rootPath, string $targetPath, array $bundlesMetadata): void
    {
        $finder = new Finder();
        $filesystem = new Filesystem();
        $paths = [];

        $finder
            ->in(array_column($bundlesMetadata, 'path'))
            ->path('Resources/encore')
            ->name($configName)
            ->files();

        /** @var \Symfony\Component\Finder\SplFileInfo $fileInfo */
        foreach ($finder as $fileInfo) {
            $paths[] = preg_replace(
                '/^' . preg_quote($rootPath, '/') . '/',
                './',
                $fileInfo->getRealPath()
            );
        }

        $filesystem->mkdir($rootPath . '/' . $targetPath);
        $filesystem->dumpFile(
            $rootPath . $targetPath . '/' . $configName,
            sprintf('module.exports = %s;', json_encode($paths))
        );
    }

    private function shouldLoadTestServices(ContainerBuilder $container): bool
    {
        return $container->hasParameter('ibexa.testing.browser.enabled')
            && true === $container->getParameter('ibexa.testing.browser.enabled');
    }
}

class_alias(IbexaAdminUiExtension::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\EzPlatformAdminUiExtension');
