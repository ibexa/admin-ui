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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\LinkComponent} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\TwigComponents\Component\LinkComponent} instead
 */
class LinkComponent implements ComponentInterface
{
    protected Environment $twig;

    protected string $href;

    protected string $type;

    protected string $rel;

    protected ?string $crossorigin;

    protected ?string $integrity;

    /**
     * @param \Twig\Environment $twig
     * @param string $href
     * @param string $type
     * @param string $rel
     * @param string|null $crossorigin
     * @param string|null $integrity
     */
    public function __construct(
        Environment $twig,
        string $href,
        string $type = 'text/css',
        string $rel = 'stylesheet',
        string $crossorigin = null,
        string $integrity = null
    ) {
        $this->twig = $twig;
        $this->href = $href;
        $this->type = $type;
        $this->rel = $rel;
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
        return $this->twig->render('@ibexadesign/ui/component/link.html.twig', [
            'href' => $this->href,
            'type' => $this->type,
            'rel' => $this->rel,
            'crossorigin' => $this->crossorigin,
            'integrity' => $this->integrity,
        ] + $parameters);
    }
}
