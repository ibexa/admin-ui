<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\UniversalDiscovery;

use Ibexa\Contracts\AdminUi\UniversalDiscovery\Provider;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;

final class UniversalDiscoveryProviderTest extends IbexaKernelTestCase
{
    // one more than the default search limit (Query::$limit = 25)
    private const LOCATIONS_COUNT = 26;

    private Provider $provider;

    protected function setUp(): void
    {
        self::bootKernel();
        self::setAdministratorUser();

        $this->provider = self::getServiceByClassName(Provider::class);
    }

    public function testGetLocationsReturnsAllRequestedLocations(): void
    {
        $locationIds = [];
        for ($i = 1; $i <= self::LOCATIONS_COUNT; ++$i) {
            $locationIds[] = $this->createFolder('Folder ' . $i);
        }

        $locations = $this->provider->getLocations($locationIds);

        $returnedLocationIds = array_map(
            static fn (array $item): int => $item['location']->id,
            $locations
        );
        sort($returnedLocationIds);

        self::assertSame($locationIds, $returnedLocationIds);
    }

    private function createFolder(string $name): int
    {
        $contentService = self::getContentService();
        $locationService = self::getLocationService();

        $createStruct = $contentService->newContentCreateStruct(
            self::getContentTypeService()->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );
        $createStruct->setField('name', $name);

        $draft = $contentService->createContent(
            $createStruct,
            [$locationService->newLocationCreateStruct(2)]
        );
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $mainLocationId = $content->getContentInfo()->getMainLocationId();
        self::assertNotNull($mainLocationId);

        return $mainLocationId;
    }
}
