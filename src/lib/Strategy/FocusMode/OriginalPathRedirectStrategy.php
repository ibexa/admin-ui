<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Strategy\FocusMode;

use Ibexa\Contracts\AdminUi\FocusMode\RedirectStrategyInterface;

final class OriginalPathRedirectStrategy implements RedirectStrategyInterface
{
    /**
     * @param array<string, string> $routeData
     */
    public function supports(array $routeData): bool
    {
        ['_route' => $route] = $routeData;

        return $route === 'ibexa.content.view';
    }

    public function generateRedirectPath(string $originalPath): string
    {
        return $originalPath;
    }
}
