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

final class UniversalDiscoveryExtension extends AbstractExtension
{
    public function __construct(
        private readonly ConfigResolver $udwConfigResolver
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_udw_config',
                $this->renderUniversalDiscoveryWidgetConfig(...),
                ['is_safe' => ['json']]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderUniversalDiscoveryWidgetConfig(string $configName, array $context = []): string
    {
        $config = $this->udwConfigResolver->getConfig($configName, $context);

        $normalized = $this->recursiveConfigurationArrayNormalize($config);

        return json_encode($normalized) ?: '';
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
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

    private function toCamelCase(string $input): string
    {
        $words = explode('_', ucwords($input, '_'));

        return lcfirst(implode('', $words));
    }
}
