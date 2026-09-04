<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Test\Core\Bootstrapper\Bootstrapper;

$packageRoot = dirname(__DIR__, 2);
require_once $packageRoot . '/vendor/autoload.php';

chdir($packageRoot);

(new Bootstrapper())->bootstrap();
