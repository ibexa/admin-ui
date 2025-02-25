<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Rector\Factory\IbexaRectorConfigFactory;

return (new IbexaRectorConfigFactory(
    [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]
))->createConfig();
