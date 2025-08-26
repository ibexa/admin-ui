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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\TwigComponent} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\TwigComponents\Component\TemplateComponent} instead
 */
readonly class TwigComponent implements ComponentInterface
{
    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        protected Environment $twig,
        protected string $template,
        protected array $parameters = []
    ) {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function render(array $parameters = []): string
    {
        return $this->twig->render(
            $this->template,
            $parameters + $this->parameters
        );
    }
}
