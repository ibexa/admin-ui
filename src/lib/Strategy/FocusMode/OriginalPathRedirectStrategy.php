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
    public function supports(string $route): bool
    {
        return $route === 'ibexa.content.view';
    }

    public function generateRedirectPath(string $originalPath): string
    {
        return $originalPath;
    }
}
