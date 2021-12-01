<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use eZ\Publish\API\Repository\Values\User\Limitation;
use Ibexa\AdminUi\Limitation\Templating\LimitationBlockRendererInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LimitationValueRenderingExtension extends AbstractExtension
{
    /** @var \EzSystems\EzPlatformAdminUi\Limitation\Templating\LimitationBlockRenderer */
    private $limitationRenderer;

    public function __construct(LimitationBlockRendererInterface $limitationRenderer)
    {
        $this->limitationRenderer = $limitationRenderer;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        $limitationValueCallable = function (Environment $twig, Limitation $limitation, array $params = []) {
            return $this->limitationRenderer->renderLimitationValue($limitation, $params);
        };

        return [
            new TwigFunction(
                'ez_render_limitation_value',
                $limitationValueCallable,
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                    'deprecated' => '4.0',
                    'alternative' => 'ibexa_render_limitation_value',
                ]
            ),
            new TwigFunction(
                'ibexa_render_limitation_value',
                $limitationValueCallable,
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }
}

class_alias(LimitationValueRenderingExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\LimitationValueRenderingExtension');
