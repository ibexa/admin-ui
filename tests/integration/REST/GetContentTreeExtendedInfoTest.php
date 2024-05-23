<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /location/tree/{locationId}/extended-info REST endpoint.
 */
final class GetContentTreeExtendedInfoTest extends BaseAdminUiRestWebTestCase
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
                    'content/create' => [
                        new ContentTypeLimitation(
                            ['limitationValues' => ['1', '16']]
                        ),
                        new LanguageLimitation(
                            ['limitationValues' => ['eng-GB', 'eng-US']]
                        ),
                    ],
                    'content/edit' => [
                        new LanguageLimitation(
                            ['limitationValues' => ['eng-GB']]
                        ),
                    ],
                ]
            )
        );
    }

    protected static function getEndpointsToTest(): iterable
    {
        foreach (self::REQUIRED_FORMATS as $format) {
            yield new EndpointRequestDefinition(
                'GET',
                '/api/ibexa/v2/location/tree/2/extended-info',
                'ContentTreeNodeExtendedInfo',
                "application/vnd.ibexa.api.ContentTreeNodeExtendedInfo+$format",
                ['HTTP_X-SiteAccess' => 'admin'],
                null,
                null,
                'ContentTreeNodeExtendedInfo'
            );
        }
    }
}
