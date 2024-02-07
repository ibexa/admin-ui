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
    'class_attributes_separation' => [
        'elements' => [
            'method' => 'one',
            'property' => 'one',
        ],
    ],
    'class_definition' => [
        'single_item_single_line' => true,
        'inline_constructor_arguments' => false,
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
