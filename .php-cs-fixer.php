<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\CodeStyle\PhpCsFixer\InternalConfigFactory;

$configFactory = new InternalConfigFactory();
$configFactory->withRules([
    'declare_strict_types' => false,
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'self',
        // createStub() is not static in PHPUnit 9
        'methods' => ['createStub' => 'this'],
    ],
]);

return $configFactory
    ->buildConfig()
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(
                array_filter([
                    __DIR__ . '/src',
                    __DIR__ . '/tests',
                ], 'is_dir')
            )
            ->files()->name('*.php')
    );
