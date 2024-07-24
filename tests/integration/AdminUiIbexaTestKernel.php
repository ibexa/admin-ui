<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle;
use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Bundle\ContentForms\IbexaContentFormsBundle;
use Ibexa\Bundle\DesignEngine\IbexaDesignEngineBundle;
use Ibexa\Bundle\Notifications\IbexaNotificationsBundle;
use Ibexa\Bundle\Rest\IbexaRestBundle;
use Ibexa\Bundle\Search\IbexaSearchBundle;
use Ibexa\Bundle\Test\Rest\IbexaTestRestBundle;
use Ibexa\Bundle\User\IbexaUserBundle;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Test\Core\IbexaTestKernel;
use Ibexa\Rest\Server\Controller\JWT;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

/**
 * @internal
 */
final class AdminUiIbexaTestKernel extends IbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new HautelookTemplatedUriBundle();
        yield new KnpMenuBundle();
        yield new WebpackEncoreBundle();
        yield new SensioFrameworkExtraBundle();
        yield new DAMADoctrineTestBundle();

        yield new IbexaContentFormsBundle();
        yield new IbexaDesignEngineBundle();
        yield new IbexaRestBundle();
        yield new IbexaSearchBundle();
        yield new IbexaTestRestBundle();
        yield new IbexaUserBundle();
        yield new IbexaNotificationsBundle();

        yield new IbexaAdminUiBundle();
    }

    protected static function getExposedServicesByClass(): iterable
    {
        yield from parent::getExposedServicesByClass();

        yield BookmarkService::class;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/Resources/ibexa.yaml');

        $loader->load(static function (ContainerBuilder $container): void {
            self::configureIbexaBundles($container);
            self::configureThirdPartyBundles($container);
        });
    }

    private static function configureIbexaBundles(ContainerBuilder $container): void
    {
        // REST
        $resource = new FileResource(__DIR__ . '/Resources/routing.yaml');
        $container->addResource($resource);
        $container->loadFromExtension('framework', [
            'router' => [
                'resource' => $resource->getResource(),
            ],
        ]);
        self::addSyntheticService($container, JWT::class);

        $configFileName = __DIR__ . '/Resources/ibexa_test_config.yaml';
        $resource = new FileResource($configFileName);
        $container->addResource($resource);

        $extensionConfig = Yaml::parseFile($resource->getResource());
        foreach ($extensionConfig as $extensionName => $config) {
            $container->loadFromExtension($extensionName, $config);
        }
    }

    private static function configureThirdPartyBundles(ContainerBuilder $container): void
    {
        $container->loadFromExtension('webpack_encore', [
            'output_path' => dirname(__DIR__, 2) . '/var/encore/output',
        ]);

        // bazinga's locale_fallback
        $container->setParameter('locale_fallback', 'en');

        // Symfony
        $container->setParameter('form.type_extension.csrf.enabled', false);
        $container->setParameter('fos_http_cache.tag_handler.strict', false);
        $container->setParameter('fos_http_cache.compiler_pass.tag_annotations', false);
    }
}
