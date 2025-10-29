<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\DataMapper\ContentMainLocationUpdateMapper;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\AdminUi\Form\DataMapper\ContentMainLocationUpdateMapper
 */
final class ContentMainLocationUpdateMapperTest extends TestCase
{
    /** @var LocationService&MockObject */
    private LocationService $locationService;

    private ContentMainLocationUpdateMapper $mapper;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
        $this->mapper = new ContentMainLocationUpdateMapper($this->locationService);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function testMapWithMainLocationId(): void
    {
        $mainLocationId = 42;
        $location = $this->createMock(Location::class);

        $struct = new ContentMetadataUpdateStruct(['mainLocationId' => $mainLocationId]);

        $this->locationService
            ->expects(self::once())
            ->method('loadLocation')
            ->with($mainLocationId)
            ->willReturn($location);

        $data = $this->mapper->map($struct);

        self::assertSame($location, $data->getLocation());
    }

    /**
     * @throws NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function testMapWithNullMainLocationId(): void
    {
        $struct = new ContentMetadataUpdateStruct(['mainLocationId' => null]);

        $this->locationService
            ->expects(self::never())
            ->method('loadLocation');

        $data = $this->mapper->map($struct);

        self::assertNull($data->getLocation());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function testMapThrowsOnInvalidValueObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mapper->map($this->createMock(ValueObject::class));
    }
}
