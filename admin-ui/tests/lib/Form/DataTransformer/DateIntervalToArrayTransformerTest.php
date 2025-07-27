<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use DateInterval;
use Ibexa\AdminUi\Form\DataTransformer\DateIntervalToArrayTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type TDataIntervalArray array{year: string, month: string, day: string, hour: string, minute: string, second: string}
 * @phpstan-type TDataIntervalPair array<string, TDataIntervalArray>
 */
class DateIntervalToArrayTransformerTest extends TestCase
{
    /**
     * @phpstan-return list<array{TDataIntervalPair}>
     */
    public function transformProvider(): array
    {
        return [
            [
                ['P1Y2M3DT4H5M6S' => ['year' => '1', 'month' => '2', 'day' => '3', 'hour' => '4', 'minute' => '5', 'second' => '6']],
            ],
            [
                ['P42D' => ['year' => '0', 'month' => '0', 'day' => '42', 'hour' => '0', 'minute' => '0', 'second' => '0']],
            ],
            [
                ['PT12H5M' => ['year' => '0', 'month' => '0', 'day' => '0', 'hour' => '12', 'minute' => '5', 'second' => '0']],
            ],
            [
                ['P0Y' => ['year' => '0', 'month' => '0', 'day' => '0', 'hour' => '0', 'minute' => '0', 'second' => '0']],
            ],
            [
                ['PT0S' => ['year' => '0', 'month' => '0', 'day' => '0', 'hour' => '0', 'minute' => '0', 'second' => '0']],
            ],
        ];
    }

    /**
     * @dataProvider transformProvider
     *
     * @phpstan-param TDataIntervalPair $valueAsArray
     */
    public function testTransform(array $valueAsArray): void
    {
        $transformer = new DateIntervalToArrayTransformer();
        $value = new DateInterval(array_keys($valueAsArray)[0]);
        self::assertSame($valueAsArray[array_keys($valueAsArray)[0]], $transformer->transform($value));
    }

    /**
     * @dataProvider transformProvider
     *
     * @phpstan-param TDataIntervalPair $valueAsArray
     */
    public function testReverseTransform(array $valueAsArray): void
    {
        $transformer = new DateIntervalToArrayTransformer();
        $expectedValue = new DateInterval(array_keys($valueAsArray)[0]);
        self::assertEquals($expectedValue, $transformer->reverseTransform($valueAsArray[array_keys($valueAsArray)[0]]));
    }

    public function testTransformNull(): void
    {
        $transformer = new DateIntervalToArrayTransformer();
        self::assertSame(
            ['year' => '0', 'month' => '0', 'day' => '0', 'hour' => '0', 'minute' => '0', 'second' => '0'],
            $transformer->transform(null)
        );
    }

    /**
     * @phpstan-return list<array{mixed}>
     */
    public function reverseTransformNullProvider(): array
    {
        return [
            [null],
            [['']],
            [['', '', '']],
            [['', null, '']],
        ];
    }

    /**
     * @dataProvider reverseTransformNullProvider
     *
     * @phpstan-param array{mixed}|null $value
     */
    public function testReverseTransformNull(?array $value): void
    {
        $transformer = new DateIntervalToArrayTransformer();
        self::assertNull($transformer->reverseTransform($value));
    }
}
