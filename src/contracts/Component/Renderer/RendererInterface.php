<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Component\Renderer;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\Contracts\AdminUi\Component\Renderer\RendererInterface} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\Renderer\RendererInterface} instead
 */
interface RendererInterface
{
    public function renderGroup(string $groupName, array $parameters = []): array;

    public function renderSingle(string $name, $groupName, array $parameters = []): string;
}
