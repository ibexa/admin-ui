<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Core\Test\Persistence\Fixture\FixtureImporter;
use Ibexa\Tests\Core\Repository\LegacySchemaImporter;
use Ibexa\Tests\Integration\AdminUi\AdminUiIbexaTestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

chdir(dirname(__DIR__, 2));

$kernel = new AdminUiIbexaTestKernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);

if (getenv('DATABASE_URL') !== false && strpos(getenv('DATABASE_URL'), 'sqlite') !== 0) {
    $application->run(new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--if-exists' => '1',
        '--force' => '1',
        '--quiet' => true,
    ]));
}

$application->run(new ArrayInput([
    'command' => 'doctrine:database:create',
    '--quiet' => true,
]));

/** @var \Psr\Container\ContainerInterface $testContainer */
$testContainer = $kernel->getContainer()->get('test.service_container');

$schemaImporter = $testContainer->get(LegacySchemaImporter::class);
foreach ($kernel->getSchemaFiles() as $file) {
    $schemaImporter->importSchema($file);
}

$fixtureImporter = $testContainer->get(FixtureImporter::class);
foreach ($kernel->getFixtures() as $fixture) {
    $fixtureImporter->import($fixture);
}
$kernel->shutdown();
