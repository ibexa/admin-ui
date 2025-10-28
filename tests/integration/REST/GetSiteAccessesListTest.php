<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /siteaccess/load-for-location/{locationId} REST endpoint.
 */
final class GetSiteAccessesListTest extends BaseAdminUiRestWebTestCase
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // to create a new user before logging-in via REST
        $this->getIbexaTestCore()->setAdministratorUser();

        $this->loginAsUser(
            $this->createUserWithPolicies(
                'editor',
                [
                    'user/login' => [],
                    'content/read' => [],
                    'content/versionread' => [],
                ]
            )
        );
    }

    protected static function getEndpointsToTest(): iterable
    {
        foreach (self::REQUIRED_FORMATS as $format) {
            yield new EndpointRequestDefinition(
                'GET',
                '/api/ibexa/v2/siteaccess/load-for-location/2',
                'SiteAccessesList',
                "application/vnd.ibexa.api.SiteAccessesList+$format",
                ['HTTP_X-SiteAccess' => 'admin'],
                null,
                null,
                'SiteAccessesList'
            );
        }
    }
}
