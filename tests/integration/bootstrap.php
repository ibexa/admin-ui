<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Test\Core\Bootstrapper\Bootstrapper;
use Ibexa\Contracts\Test\Core\Bootstrapper\SchemaInstallPath;

$packageRoot = dirname(__DIR__, 2);
require_once $packageRoot . '/vendor/autoload.php';

chdir($packageRoot);

// Selects which of the two coexisting schema install paths this run exercises: the
// SchemaBuilderEvent one by default, or the Doctrine Migrations one with
// IBEXA_TEST_SCHEMA_BUILDER_EVENT_ENABLED=0. The base fixture replaces the content ibexa/core's
// ImportDataMigration inserts, so the tests see the same data either way.
$schemaBuilderEventEnabled = getenv('IBEXA_TEST_SCHEMA_BUILDER_EVENT_ENABLED') !== '0';

(new Bootstrapper())->bootstrap(null, SchemaInstallPath::getBootstrapperOptions($schemaBuilderEventEnabled) + [
    Bootstrapper::class => [
        // this package's tests never ran doctrine:schema:update, and Bootstrapper enables it by
        // default; keep it off so the bootstrap stays equivalent
        Bootstrapper::OPTION_SCHEMA_UPDATE => false,
    ],
]);
