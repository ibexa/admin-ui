<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\SiteAccessLimitationMapper;
use Ibexa\AdminUi\Siteaccess\SiteAccessKeyGenerator;
use Ibexa\AdminUi\Siteaccess\SiteAccessKeyGeneratorInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SiteAccessLimitation;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use PHPUnit\Framework\TestCase;

class SiteAccessLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue()
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
            ->willReturn(new SiteAccessKeyGenerator());

        $siteAccessService = $this->createMock(SiteAccessServiceInterface::class);
        $siteAccessService->method('getAll')->willReturn($siteAccesses);

        $mapper = new SiteAccessLimitationMapper($siteAccessService, $siteAccessesGeneratorInterface);
        $result = $mapper->mapLimitationValue($limitation);

        $this->assertEquals(array_values($siteAccessList), $result);
    }
}

class_alias(SiteAccessLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\SiteAccessLimitationMapperTest');
