<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\Autosave\AutosaveServiceInterface;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

class Autosave implements ProviderInterface
{
    private AutosaveServiceInterface $autosaveService;

    public function __construct(AutosaveServiceInterface $autosaveService)
    {
        $this->autosaveService = $autosaveService;
    }

    public function getConfig(): array
    {
        return [
            'enabled' => $this->autosaveService->isEnabled(),
            'interval' => $this->autosaveService->getInterval(),
        ];
    }
}
