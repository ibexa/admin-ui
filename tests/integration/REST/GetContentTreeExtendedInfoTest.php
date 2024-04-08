<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /location/content-tree/{locationId}/extended-info REST endpoint.
 */
final class GetContentTreeExtendedInfoTest extends BaseAdminUiRestWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $ibexaTestCore = $this->getIbexaTestCore();
        $ibexaTestCore->setAdministratorUser();
    }

    protected static function getEndpointsToTest(): iterable
    {
        yield new EndpointRequestDefinition(
            'GET',
            '/api/ibexa/v2/location/content-tree/2/extended-info',
            'ContentTreeNodeExtendedInfo',
            'application/vnd.ibexa.api.ContentTreeNodeExtendedInfo+json',
            ['HTTP_X-SiteAccess' => 'admin'],
            null,
            null,
            'ContentTreeNodeExtendedInfo'
        );
    }
}
