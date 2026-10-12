<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

// Two autoloaders: this directory's own (PHPUnit + the shared library), and
// the assembled project's (App\Kernel + the full app) four levels up — the
// path ci-scripts' prepare_project_edition.sh produces. No App\Kernel means
// no assembled project, so this doesn't run standalone outside CI.

require __DIR__ . '/vendor/autoload.php';

$assembledProjectRoot = __DIR__ . '/../../../../..';
$assembledProjectAutoload = $assembledProjectRoot . '/vendor/autoload.php';
if (is_file($assembledProjectAutoload)) {
    require $assembledProjectAutoload;

    // Needed for env(...) references in the assembled project's own config
    // (e.g. SEARCH_ENGINE) — requiring its autoloader alone doesn't load .env.
    if (class_exists(Dotenv::class) && method_exists(Dotenv::class, 'bootEnv')) {
        (new Dotenv())->bootEnv($assembledProjectRoot . '/.env');
    }
}
