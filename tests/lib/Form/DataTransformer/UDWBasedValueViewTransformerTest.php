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
use Ibexa\Core\Repository\Values\Content\Location as CoreLocation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class UDWBasedValueViewTransformerTest extends TestCase
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
     * @param mixed[] $given
     */
    #[DataProvider('dataProviderForTransform')]
    public function testTransform(?array $given, ?string $expected): void
    {
        self::assertEquals($expected, $this->transformer->transform($given));
    }

    /**
     * @return array<array{0: ?array<Location>, 1: ?string}>
     */
    public static function dataProviderForTransform(): array
    {
        return [
            [null, null],
            [
                [
                    self::createLocation(54),
                    self::createLocation(56),
                    self::createLocation(58),
                ],
                '54,56,58',
            ],
        ];
    }

    /**
     * @param mixed[] $expected
     */
    #[DataProvider('dataProviderForReverseTransform')]
    public function testReverseTransform(?string $given, ?array $expected): void
    {
        $this->locationService
            ->method('loadLocation')
            ->willReturnCallback(static function ($id): Location {
                return self::createLocation($id);
            });

        self::assertEquals($expected, $this->transformer->reverseTransform($given));
    }

    /**
     * @return array<array{0: ?string, 1: ?array<\Ibexa\Contracts\Core\Repository\Values\Content\Location>}>
     */
    public static function dataProviderForReverseTransform(): array
    {
        return [
            [null, null],
            [
                '54,56,58',
                [
                    self::createLocation(54),
                    self::createLocation(56),
                    self::createLocation(58),
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
                self::createStub(UnauthorizedException::class)
            );

        $this->transformer->reverseTransform('54,56,58');
    }

    private static function createLocation(int $id): Location
    {
        return new CoreLocation(['id' => $id]);
    }
}
