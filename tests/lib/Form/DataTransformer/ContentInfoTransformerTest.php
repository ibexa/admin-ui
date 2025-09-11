<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\ContentInfoTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ContentInfoTransformerTest extends TestCase
{
    private const int EXAMPLE_CONTENT_ID = 123456;

    private ContentInfoTransformer $contentInfoTransformer;

    protected function setUp(): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject $contentService */
        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->method('loadContentInfo')
            ->with(self::logicalAnd(
                self::equalTo(self::EXAMPLE_CONTENT_ID),
                self::isType('int')
            ))
            ->willReturn(new ContentInfo([
                'id' => self::EXAMPLE_CONTENT_ID,
            ]));

        $this->contentInfoTransformer = new ContentInfoTransformer($contentService);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . ContentInfo::class . ' object.');

        $result = $this->contentInfoTransformer->transform($value);
        self::assertNull($result);
    }

    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(?ContentInfo $value, ?int $expected): void
    {
        $result = $this->contentInfoTransformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider reverseTransformDataProvider
     */
    public function testReverseTransform(mixed $value, ?ContentInfo $expected): void
    {
        $result = $this->contentInfoTransformer->reverseTransform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $this->contentInfoTransformer->reverseTransform($value);
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('ContentInfo not found');

        /** @var \Ibexa\Contracts\Core\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject $service */
        $service = $this->createMock(ContentService::class);
        $service->method('loadContentInfo')
            ->will(self::throwException(new class('ContentInfo not found') extends NotFoundException {
            }));

        $transformer = new ContentInfoTransformer($service);

        $transformer->reverseTransform(654321);
    }

    /**
     * @return array<string, array{\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null, int|null}>
     */
    public function transformDataProvider(): array
    {
        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        return [
            'content_info_with_id' => [$contentInfo, self::EXAMPLE_CONTENT_ID],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed, \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null}>
     */
    public function reverseTransformDataProvider(): array
    {
        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        return [
            'integer' => [self::EXAMPLE_CONTENT_ID, $contentInfo],
            'string' => [(string)self::EXAMPLE_CONTENT_ID, $contentInfo],
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

    /**
     * @return array<string, array{mixed}>
     */
    public function reverseTransformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'bool' => [true],
            'array' => [['element']],
            'object' => [new \stdClass()],
            'content_info' => [new ContentInfo()],
        ];
    }
}
