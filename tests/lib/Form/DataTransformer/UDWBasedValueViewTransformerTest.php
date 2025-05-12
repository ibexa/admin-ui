<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\UDWBasedValueViewTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UDWBasedValueViewTransformerTest extends TestCase
{
    private LocationService&MockObject $locationService;

    private UDWBasedValueViewTransformer $transformer;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
        $this->transformer = new UDWBasedValueViewTransformer(
            $this->locationService
        );
    }

    /**
     * @dataProvider dataProviderForTransform
     */
    public function testTransform(?array $given, ?string $expected): void
    {
        self::assertEquals($expected, $this->transformer->transform($given));
    }

    public function dataProviderForTransform(): array
    {
        return [
            [null, null],
            [
                [
                    $this->createLocation(54),
                    $this->createLocation(56),
                    $this->createLocation(58),
                ],
                '54,56,58',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForReverseTransform
     */
    public function testReverseTransform(?string $given, ?array $expected): void
    {
        $this->locationService
            ->method('loadLocation')
            ->willReturnCallback(function ($id): Location {
                return $this->createLocation($id);
            });

        self::assertEquals($expected, $this->transformer->reverseTransform($given));
    }

    public function dataProviderForReverseTransform(): array
    {
        return [
            [null, null],
            [
                '54,56,58',
                [
                    $this->createLocation(54),
                    $this->createLocation(56),
                    $this->createLocation(58),
                ],
            ],
        ];
    }

    public function testReverseTransformThrowsTransformationFailedException(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->locationService
            ->method('loadLocation')
            ->willThrowException(
                $this->createMock(UnauthorizedException::class)
            );

        $this->transformer->reverseTransform('54,56,58');
    }

    private function createLocation($id): Location
    {
        $location = $this->createMock(Location::class);
        $location
            ->method('__get')
            ->with('id')
            ->willReturn($id);
        $location
            ->method('__isset')
            ->with('id')
            ->willReturn(true);

        return $location;
    }
}
