<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component;

use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Twig\Environment;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\ScriptComponent} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\TwigComponents\Component\ScriptComponent} instead
 */
class ScriptComponent implements ComponentInterface
{
    /** @var \Twig\Environment */
    protected $twig;

    /** @var string */
    protected $src;

    /** @var string */
    protected $type;

    /** @var string|null */
    protected $async;

    /** @var string|null */
    protected $defer;

    /** @var string|null */
    protected $crossorigin;

    /** @var string|null */
    protected $integrity;

    public function __construct(
        Environment $twig,
        string $src,
        string $type = 'text/javascript',
        ?string $async = null,
        ?string $defer = null,
        ?string $crossorigin = null,
        ?string $integrity = null
    ) {
        $this->twig = $twig;
        $this->src = $src;
        $this->type = $type;
        $this->async = $async;
        $this->defer = $defer;
        $this->crossorigin = $crossorigin;
        $this->integrity = $integrity;
    }

    /**
     * @param array $parameters
     */
    public function render(array $parameters = []): string
    {
        return $this->twig->render('@ibexadesign/ui/component/script.html.twig', [
            'src' => $this->src,
            'type' => $this->type,
            'async' => $this->async,
            'defer' => $this->defer,
            'crossorigin' => $this->crossorigin,
            'integrity' => $this->integrity,
        ] + $parameters);
    }
}

class_alias(ScriptComponent::class, 'EzSystems\EzPlatformAdminUi\Component\ScriptComponent');
