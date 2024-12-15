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
use Symfony\Component\Form\Exception\TransformationFailedException;

final class DateTimePickerTransformerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTransform
     */
    public function testTransform(): void
    {
        $transformer = new DateTimePickerTransformer();
        $dateTime = new DateTime('2021-01-01 00:00:00');
        $this->assertSame($dateTime->getTimestamp(), $transformer->transform($dateTime));
    }

    /**
     * @return iterable<string, array{mixed, ?int}>
     */
    public function dataProviderForTransform(): iterable
    {
        yield 'null' => [null, null];
        yield 'DateTime' => [new DateTime('2021-01-01 00:00:00'), 1609459200];
        yield 'DateTimeImmutable' => [new DateTimeImmutable('2021-01-01 00:00:00'), 1609459200];
    }

    public function testTransformWithInvalidValue(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Found string instead of DateTimeInterface');

        $transformer = new DateTimePickerTransformer();
        $transformer->transform('invalid');
    }

    /**
     * @dataProvider dataProviderForReverseTransform
     */
    public function testReverseTransform(): void
    {
        $transformer = new DateTimePickerTransformer();
        $dateTime = new DateTime('2021-01-01 00:00:00');
        $this->assertEquals($dateTime, $transformer->reverseTransform($dateTime->getTimestamp()));
    }

    /**
     * @return iterable<string, array{?int, ?DateTime}>
     */
    public function dataProviderForReverseTransform(): iterable
    {
        yield 'null' => [null, null];
        yield 'DateTime' => [1609459200, new DateTime('2021-01-01 00:00:00')];
    }

    public function testReverseTransformWithInvalidValue(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Found string instead of a numeric value');

        $transformer = new DateTimePickerTransformer();
        $transformer->reverseTransform('invalid');
    }
}
