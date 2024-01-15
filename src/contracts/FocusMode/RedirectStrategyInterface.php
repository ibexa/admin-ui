<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\FocusMode;

interface RedirectStrategyInterface
{
    /**
     * @param array<string, string> $routeData
     */
    public function supports(array $routeData): bool;

    public function generateRedirectPath(string $originalPath): string;
}
