<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\SectionsTransformer;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section as APISection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SectionsTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(mixed $value, ?string $expected): void
    {
        $service = $this->createMock(SectionService::class);
        $transformer = new SectionsTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    public function testReverseTransformWithIds(): void
    {
        $service = $this->createMock(SectionService::class);
        $service->expects(self::exactly(2))
            ->method('loadSection')
            ->willReturnMap([
                [123456, new APISection(['id' => 123456])],
                [456789, new APISection(['id' => 456789])],
            ]);

        $transformer = new SectionsTransformer($service);
        $result = $transformer->reverseTransform('123456,456789');

        self::assertEquals([new APISection(['id' => 123456]), new APISection(['id' => 456789])], $result);
    }

    /**
     * @dataProvider reverseTransformWithEmptyDataProvider
     */
    public function testReverseTransformWithEmpty(mixed $value): void
    {
        $service = $this->createMock(SectionService::class);
        $service->expects(self::never())
            ->method('loadSection');

        $transformer = new SectionsTransformer($service);
        $result = $transformer->reverseTransform($value);

        self::assertNull($result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $service = $this->createMock(SectionService::class);
        $transformer = new SectionsTransformer($service);

        $transformer->reverseTransform($value);
    }

    /**
     * @return array<string, array{\Ibexa\Contracts\Core\Repository\Values\Content\Section[]|string|null, string|null}>
     */
    public function transformDataProvider(): array
    {
        $sectionA = new APISection(['id' => 123456]);
        $sectionB = new APISection(['id' => 456789]);

        return [
            'with_array_of_ids' => [[$sectionA, $sectionB], '123456,456789'],
            'with_array_of_id' => [[$sectionA], '123456'],
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
