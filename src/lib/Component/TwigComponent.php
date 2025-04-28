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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\TwigComponent} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\TwigComponents\Component\TemplateComponent} instead
 */
class TwigComponent implements ComponentInterface
{
    /** @var string */
    protected $template;

    /** @var \Twig\Environment */
    protected $twig;

    /** @var array */
    protected $parameters;

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

class_alias(TwigComponent::class, 'EzSystems\EzPlatformAdminUi\Component\TwigComponent');
