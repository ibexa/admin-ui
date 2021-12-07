<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UniversalDiscovery\ConfigResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UniversalDiscoveryExtension extends AbstractExtension
{
    /** @var \Ibexa\AdminUi\UniversalDiscovery\ConfigResolver */
    protected $udwConfigResolver;

    /**
     * @param \Ibexa\AdminUi\UniversalDiscovery\ConfigResolver $udwConfigResolver
     */
    public function __construct(
        ConfigResolver $udwConfigResolver
    ) {
        $this->udwConfigResolver = $udwConfigResolver;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'ez_udw_config',
                [$this, 'renderUniversalDiscoveryWidgetConfig'],
                [
                    'is_safe' => ['json'],
                    'deprecated' => '4.0',
                    'alternative' => 'ibexa_udw_config',
                ]
            ),
            new TwigFunction(
                'ibexa_udw_config',
                [$this, 'renderUniversalDiscoveryWidgetConfig'],
                ['is_safe' => ['json']]
            ),
        ];
    }

    /**
     * @param string $configName
     * @param array $context
     *
     * @return string
     */
    public function renderUniversalDiscoveryWidgetConfig(string $configName, array $context = []): string
    {
        $config = $this->udwConfigResolver->getConfig($configName, $context);

        $normalized = $this->recursiveConfigurationArrayNormalize($config);

        return json_encode($normalized);
    }

    private function recursiveConfigurationArrayNormalize(array $config): array
    {
        $normalized = [];

        foreach ($config as $key => $value) {
            $normalizedKey = !is_numeric($key) ? $this->toCamelCase($key) : $key;
            $normalizedValue = is_array($value) ? $this->recursiveConfigurationArrayNormalize($value) : $value;

            $normalized[$normalizedKey] = $normalizedValue;
        }

        return $normalized;
    }

    private function toCamelCase(string $input, string $delimiter = '_'): string
    {
        $words = explode($delimiter, ucwords($input, $delimiter));

        return lcfirst(implode('', $words));
    }
}

class_alias(UniversalDiscoveryExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\UniversalDiscoveryExtension');
