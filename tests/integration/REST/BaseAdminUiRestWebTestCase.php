<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\BaseRestWebTestCase;

/**
 * Requires \Ibexa\Tests\Integration\AdminUi\AdminUiIbexaTestKernel kernel.
 *
 * @see \Ibexa\Tests\Integration\AdminUi\AdminUiIbexaTestKernel
 */
abstract class BaseAdminUiRestWebTestCase extends BaseRestWebTestCase
{
    protected function getSchemaFileBasePath(string $resourceType, string $format): string
    {
        return dirname(__DIR__) . '/Resources/REST/Schemas/' . $resourceType;
    }

    protected static function getSnapshotDirectory(): ?string
    {
        return dirname(__DIR__) . '/Resources/REST/Snapshots';
    }
}
