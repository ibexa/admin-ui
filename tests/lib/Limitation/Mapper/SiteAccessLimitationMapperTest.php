<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\SiteAccessLimitationMapper;
use Ibexa\AdminUi\Siteaccess\SiteAccessKeyGeneratorInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SiteAccessLimitation;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use PHPUnit\Framework\TestCase;

class SiteAccessLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue(): void
    {
        $siteAccessList = [
            '2356372769' => 'foo',
            '1996459178' => 'bar',
            '2015626392' => 'baz',
        ];

        $siteAccesses = array_map(
            static function (string $siteAccessName): SiteAccess {
                return new SiteAccess($siteAccessName);
            },
            $siteAccessList
        );

        $limitation = new SiteAccessLimitation(
            [
                'limitationValues' => array_keys($siteAccessList),
            ]
        );

        $siteAccessesGeneratorInterface = $this->createMock(SiteAccessKeyGeneratorInterface::class);
        $siteAccessesGeneratorInterface
            ->method('generate')
            // re-map SiteAccess crc32 identifiers back to string, as the keys get stored as integers
            ->willReturnOnConsecutiveCalls(...array_map('strval', array_keys($siteAccessList)));

        $siteAccessService = $this->createMock(SiteAccessServiceInterface::class);
        $siteAccessService->method('getAll')->willReturn($siteAccesses);

        $mapper = new SiteAccessLimitationMapper(
            $siteAccessService,
            $siteAccessesGeneratorInterface
        );
        $result = $mapper->mapLimitationValue($limitation);

        self::assertEquals(array_values($siteAccessList), $result);
    }
}

class_alias(SiteAccessLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\SiteAccessLimitationMapperTest');
