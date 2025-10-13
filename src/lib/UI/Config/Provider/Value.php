<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

/**
 * Simple value provider that passes on the value it is given in the constructor.
 * Can be used for container config.
 */
readonly class Value implements ProviderInterface
{
    public function __construct(protected mixed $config)
    {
    }

    public function getConfig(): mixed
    {
        return $this->config;
    }
}
