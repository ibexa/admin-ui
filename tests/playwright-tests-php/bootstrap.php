<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

// Two autoloaders: this directory's own (PHPUnit + the shared library), and
// the assembled project's (App\Kernel + the full app) four levels up — the
// path ci-scripts' prepare_project_edition.sh produces. No App\Kernel means
// no assembled project, so this doesn't run standalone outside CI.

require __DIR__ . '/vendor/autoload.php';

$assembledProjectAutoload = __DIR__ . '/../../../../autoload.php';
if (is_file($assembledProjectAutoload)) {
    require $assembledProjectAutoload;
}
