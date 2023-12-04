<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\User;

final class UserModeChangeData
{
    private ?bool $mode;

    public function __construct(?bool $data = null)
    {
        $this->mode = $data;
    }

    public function getMode(): ?bool
    {
        return $this->mode;
    }

    public function setMode(?bool $mode): void
    {
        $this->mode = $mode;
    }
}
