<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

$includes = [];
if (PHP_VERSION_ID < 8_00_00) {
    $includes[] = __DIR__ . '/phpstan-baseline-7.4.neon';
} else {
    $includes[] = __DIR__ . '/phpstan-baseline-gte-8.0.neon';
}

if (PHP_VERSION_ID < 8_04_00) {
    $includes[] = __DIR__ . '/phpstan-baseline-lte-8.3.neon';
}

$config = [];
$config['includes'] = $includes;

return $config;
