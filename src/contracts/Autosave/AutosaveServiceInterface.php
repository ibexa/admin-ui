<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Autosave;

interface AutosaveServiceInterface
{
    public function isEnabled(): bool;

    /**
     * Returns autosave interval in milliseconds.
     */
    public function getInterval(): int;

    public function isInProgress(): bool;

    /**
     * @internal
     */
    public function setInProgress(bool $isInProgress): void;
}
