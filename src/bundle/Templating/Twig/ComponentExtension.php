<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\TwigComponents\Renderer\RendererInterface;
use Ibexa\TwigComponents\Component\Registry as ComponentRegistry;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ComponentExtension extends AbstractExtension
{
    protected ComponentRegistry $registry;

    protected RendererInterface $renderer;

    public function __construct(
        ComponentRegistry $registry,
        RendererInterface $renderer
    ) {
        $this->registry = $registry;
        $this->renderer = $renderer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_render_component_group',
                $this->renderComponentGroup(...),
                array_merge(
                    ['is_safe' => ['html']],
                    $this->getDeprecationOptions('ibexa_twig_component_group')
                )
            ),
            new TwigFunction(
                'ibexa_render_component',
                $this->renderComponent(...),
                array_merge(
                    ['is_safe' => ['html']],
                    $this->getDeprecationOptions('ibexa_twig_component_group')
                )
            ),
        ];
    }

    public function renderComponentGroup(string $group, array $parameters = []): string
    {
        return implode('', $this->renderer->renderGroup($group, $parameters));
    }

    public function renderComponent(string $group, string $id, array $parameters = []): string
    {
        return $this->renderer->renderSingle($group, $id, $parameters);
    }

    /**
     * @return array{
     *     deprecation_info: \Twig\DeprecatedCallableInfo
     * }
     */
    private function getDeprecationOptions(string $newFunction): array
    {
        return [
            'deprecation_info' => new DeprecatedCallableInfo('ibexa/admin-ui', '4.6.19', $newFunction),
        ];
    }
}
