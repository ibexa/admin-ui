<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component;

use Ibexa\Contracts\AdminUi\Component\Renderable;
use Twig\Environment;

class TwigComponent implements Renderable
{
    protected string $template;

    protected Environment $twig;

    protected array $parameters;

    /**
     * @param \Twig\Environment $twig
     * @param string $template
     * @param array $parameters
     */
    public function __construct(
        Environment $twig,
        string $template,
        array $parameters = []
    ) {
        $this->twig = $twig;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function render(array $parameters = []): string
    {
        return $this->twig->render($this->template, $parameters + $this->parameters);
    }
}
