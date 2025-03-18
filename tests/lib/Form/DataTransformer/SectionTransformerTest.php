<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\SectionTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\Content\Section as APISection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SectionTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     *
     * @param $value
     * @param $expected
     */
    public function testTransform(?Section $value, ?int $expected): void
    {
        $service = $this->createMock(SectionService::class);
        $transformer = new SectionTransformer($service);

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     *
     * @param $value
     */
    public function testTransformWithInvalidInput(string|int|bool|float|\stdClass|array $value): void
    {
        $languageService = $this->createMock(SectionService::class);
        $transformer = new SectionTransformer($languageService);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . APISection::class . ' object.');

        $transformer->transform($value);
    }

    public function testReverseTransformWithId(): void
    {
        $service = $this->createMock(SectionService::class);
        $service->expects(self::once())
            ->method('loadSection')
            ->with(123456)
            ->willReturn(new APISection(['id' => 123456]));

        $transformer = new SectionTransformer($service);

        $result = $transformer->reverseTransform(123456);

        self::assertEquals(new APISection(['id' => 123456]), $result);
    }

    public function testReverseTransformWithNull(): void
    {
        $service = $this->createMock(SectionService::class);
        $service->expects(self::never())
            ->method('loadSection');

        $transformer = new SectionTransformer($service);

        $result = $transformer->reverseTransform(null);

        self::assertNull($result);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Section not found');

        $service = $this->createMock(SectionService::class);
        $service->method('loadSection')
            ->will(self::throwException(new class('Section not found') extends NotFoundException {
            }));

        $transformer = new SectionTransformer($service);

        $transformer->reverseTransform(654321);
    }

    public function testReverseTransformWithNonNumericString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $service = $this->createMock(SectionService::class);
        $service->expects(self::never())->method('loadSection');

        $transformer = new SectionTransformer($service);
        $transformer->reverseTransform('XYZ');
    }

    /**
     * @return array
     */
    public function transformDataProvider(): array
    {
        $transform = new APISection(['id' => 123456]);

        return [
            'with_id' => [$transform, 123456],
            'null' => [null, null],
        ];
    }

    /**
     * @return array
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
