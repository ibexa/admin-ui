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
readonly class ScriptComponent implements ComponentInterface
{
    public function __construct(
        protected Environment $twig,
        protected string $src,
        protected string $type = 'text/javascript',
        protected ?string $async = null,
        protected ?string $defer = null,
        protected ?string $crossorigin = null,
        protected ?string $integrity = null
    ) {
    }

    /**
     * @param array<mixed> $parameters
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
