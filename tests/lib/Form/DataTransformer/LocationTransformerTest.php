<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\LocationTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LocationTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(?Location $value, ?int $expected): void
    {
        $service = $this->createMock(LocationService::class);
        $transformer = new LocationTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $languageService = $this->createMock(LocationService::class);
        $transformer = new LocationTransformer($languageService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . APILocation::class . ' object.');

        $transformer->transform($value);
    }

    public function testReverseTransformWithId(): void
    {
        $service = $this->createMock(LocationService::class);
        $service->expects(self::once())
            ->method('loadLocation')
            ->with(123456)
            ->willReturn(new Location(['id' => 123456]));

        $transformer = new LocationTransformer($service);

        $result = $transformer->reverseTransform(123456);

        self::assertEquals(new Location(['id' => 123456]), $result);
    }

    public function testReverseTransformWithNull(): void
    {
        $service = $this->createMock(LocationService::class);
        $service->expects(self::never())
            ->method('loadLocation');

        $transformer = new LocationTransformer($service);

        $result = $transformer->reverseTransform(null);

        self::assertNull($result);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Location not found');

        $service = $this->createMock(LocationService::class);
        $service->method('loadLocation')
            ->will(self::throwException(new class('Location not found') extends NotFoundException {
            }));

        $transformer = new LocationTransformer($service);

        $transformer->reverseTransform(654321);
    }

    /**
     * @return array<string, array{Location|null, int|null}>
     */
    public function transformDataProvider(): array
    {
        $location = new Location(['id' => 123456]);

        return [
            'content_info_with_id' => [$location, 123456],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function transformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }
}
