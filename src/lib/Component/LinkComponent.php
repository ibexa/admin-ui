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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\LinkComponent} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\TwigComponents\Component\LinkComponent} instead
 */
readonly class LinkComponent implements ComponentInterface
{
    public function __construct(
        protected Environment $twig,
        protected string $href,
        protected string $type = 'text/css',
        protected string $rel = 'stylesheet',
        protected ?string $crossorigin = null,
        protected ?string $integrity = null
    ) {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function render(array $parameters = []): string
    {
        return $this->twig->render('@ibexadesign/ui/component/link.html.twig', [
            'href' => $this->href,
            'type' => $this->type,
            'rel' => $this->rel,
            'crossorigin' => $this->crossorigin,
            'integrity' => $this->integrity,
        ] + $parameters);
    }
}
