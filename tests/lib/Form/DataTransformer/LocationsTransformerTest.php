<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\LocationsTransformer;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LocationsTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(mixed $value, ?string $expected): void
    {
        $service = $this->createMock(LocationService::class);
        $transformer = new LocationsTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    public function testReverseTransformWithIds(): void
    {
        $service = $this->createMock(LocationService::class);
        $service->expects(self::exactly(2))
            ->method('loadLocation')
            ->willReturnMap([
                [123456, null, null, new Location(['id' => 123456])],
                [456789, null, null, new Location(['id' => 456789])],
            ]);

        $transformer = new LocationsTransformer($service);
        $result = $transformer->reverseTransform('123456,456789');

        self::assertEquals([new Location(['id' => 123456]), new Location(['id' => 456789])], $result);
    }

    /**
     * @dataProvider reverseTransformWithEmptyDataProvider
     */
    public function testReverseTransformWithEmpty(mixed $value): void
    {
        $service = $this->createMock(LocationService::class);
        $service->expects(self::never())
            ->method('loadLocation');

        $transformer = new LocationsTransformer($service);
        $result = $transformer->reverseTransform($value);

        self::assertEmpty($result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $service = $this->createMock(LocationService::class);
        $transformer = new LocationsTransformer($service);

        $transformer->reverseTransform($value);
    }

    /**
     * @return array<string, array{\Ibexa\Core\Repository\Values\Content\Location[]|string|null, string|null}>
     */
    public function transformDataProvider(): array
    {
        $location_1 = new Location(['id' => 123456]);
        $location_2 = new Location(['id' => 456789]);

        return [
            'with_array_of_ids' => [[$location_1, $location_2], '123456,456789'],
            'with_array_of_id' => [[$location_1], '123456'],
            'null' => [null, null],
            'string' => ['string', null],
            'empty_array' => [[], null],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function reverseTransformWithInvalidInputDataProvider(): array
    {
        return [
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [['element']],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function reverseTransformWithEmptyDataProvider(): array
    {
        return [
            'an_empty_string' => [''],
            '0_as_an_integer' => [0],
            '0_as_a_float' => [0.0],
            '0_as_a_string' => ['0'],
            'null' => [null],
            'false' => [false],
            'an_empty_array' => [[]],
        ];
    }
}
