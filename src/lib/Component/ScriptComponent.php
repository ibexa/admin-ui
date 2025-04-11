<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component;

use Ibexa\Contracts\AdminUi\Component\Renderable;
use Twig\Environment;

class ScriptComponent implements Renderable
{
    protected Environment $twig;

    protected string $src;

    protected string $type;

    protected ?string $async;

    protected ?string $defer;

    protected ?string $crossorigin;

    protected ?string $integrity;

    /**
     * @param \Twig\Environment $twig
     * @param string $src
     * @param string $type
     * @param string|null $async
     * @param string|null $defer
     * @param string|null $crossorigin
     * @param string|null $integrity
     */
    public function __construct(
        Environment $twig,
        string $src,
        string $type = 'text/javascript',
        string $async = null,
        string $defer = null,
        string $crossorigin = null,
        string $integrity = null
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
     *
     * @return string
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
