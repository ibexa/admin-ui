<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use DateTime;
use DateTimeImmutable;
use Ibexa\AdminUi\Form\DataTransformer\DateTimePickerTransformer;
use PHPUnit\Framework\TestCase;

final class DateTimePickerTransformerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestTransform
     */
    public function testTransform(): void
    {
        $transformer = new DateTimePickerTransformer();
        $dateTime = new DateTime('2021-01-01 00:00:00');
        self::assertSame($dateTime->getTimestamp(), $transformer->transform($dateTime));
    }

    /**
     * @return iterable<string, array{mixed, ?int}>
     */
    public function dataProviderForTestTransform(): iterable
    {
        yield 'null' => [null, null];
        yield 'DateTime' => [new DateTime('2021-01-01 00:00:00'), 1609459200];
        yield 'DateTimeImmutable' => [new DateTimeImmutable('2021-01-01 00:00:00'), 1609459200];
    }

    /**
     * @dataProvider dataProviderForTestReverseTransform
     */
    public function testReverseTransform(): void
    {
        $transformer = new DateTimePickerTransformer();
        $dateTime = new DateTime('2021-01-01 00:00:00');
        self::assertEquals($dateTime, $transformer->reverseTransform($dateTime->getTimestamp()));
    }

    /**
     * @return iterable<string, array{?int, ?DateTime}>
     */
    public function dataProviderForTestReverseTransform(): iterable
    {
        yield 'null' => [null, null];
        yield 'DateTime' => [1609459200, new DateTime('2021-01-01 00:00:00')];
    }
}
