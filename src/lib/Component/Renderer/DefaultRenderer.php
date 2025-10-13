<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Renderer;

use Ibexa\Contracts\AdminUi\Component\Renderer\RendererInterface;
use Ibexa\TwigComponents\Component\Renderer\DefaultRenderer as TwigComponentsDefaultRenderer;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Renderer\DefaultRenderer} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\TwigComponents\Component\Renderer\DefaultRenderer} instead
 */
readonly class DefaultRenderer implements RendererInterface
{
    public function __construct(
        private TwigComponentsDefaultRenderer $inner
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function renderGroup(string $groupName, array $parameters = []): array
    {
        return $this->inner->renderGroup($groupName, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function renderSingle(string $name, $groupName, array $parameters = []): string
    {
        return $this->inner->renderSingle($name, $groupName, $parameters);
    }
}
