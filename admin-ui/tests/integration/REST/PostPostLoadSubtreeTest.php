<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Test\Rest\Input\PayloadLoader;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /location/load-subtree REST endpoint.
 */
final class PostPostLoadSubtreeTest extends BaseAdminUiRestWebTestCase
{
    private const INPUT_MEDIA_TYPE = 'ContentTreeLoadSubtreeRequest';

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $ibexaTestCore = $this->getIbexaTestCore();
        $ibexaTestCore->setAdministratorUser();
        $bookmarkService = $ibexaTestCore->getServiceByClassName(BookmarkService::class);
        $locationService = $ibexaTestCore->getLocationService();
        $location = $locationService->loadLocation(2);
        $bookmarkService->createBookmark($location);
    }

    protected static function getEndpointsToTest(): iterable
    {
        $payloadLoader = new PayloadLoader(dirname(__DIR__) . '/Resources/REST/InputPayloads');

        yield new EndpointRequestDefinition(
            'POST',
            '/api/ibexa/v2/location/tree/load-subtree',
            'ContentTreeRoot',
            'application/vnd.ibexa.api.ContentTreeRoot+json',
            ['HTTP_X-SiteAccess' => 'admin'],
            $payloadLoader->loadPayload(self::INPUT_MEDIA_TYPE, 'json'),
            null,
            'ContentTreeRoot'
        );
    }
}
