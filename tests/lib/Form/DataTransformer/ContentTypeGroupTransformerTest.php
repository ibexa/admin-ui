<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\ContentTypeGroupTransformer;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ContentTypeGroupTransformerTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_GROUP_ID = 1;

    private ContentTypeService&MockObject $contentService;

    private ContentTypeGroupTransformer $transformer;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentTypeService::class);
        $this->transformer = new ContentTypeGroupTransformer($this->contentService);
    }

    /**
     * @dataProvider dataProviderForTransformWithValidInput
     */
    public function testTransformWithValidInput(?APIContentTypeGroup $value, ?int $expected): void
    {
        self::assertEquals($expected, $this->transformer->transform($value));
    }

    public function dataProviderForTransformWithValidInput(): array
    {
        $contentTypeGroup = new ContentTypeGroup([
            'id' => self::EXAMPLE_CONTENT_TYPE_GROUP_ID,
        ]);

        return [
            'null' => [null, null],
            'content_type_group_with_id' => [$contentTypeGroup, self::EXAMPLE_CONTENT_TYPE_GROUP_ID],
        ];
    }

    /**
     * @dataProvider dataProviderForTransformWithInvalidInput
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . APIContentTypeGroup::class . ' object.');

        $this->transformer->transform($value);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function dataProviderForTransformWithInvalidInput(): array
    {
        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }

    /**
     * @dataProvider dataProviderForReverseTransformWithValidInput
     */
    public function testReverseTransformWithValidInput(mixed $value, ?APIContentTypeGroup $expected): void
    {
        if ($expected !== null) {
            $this->contentService
                ->method('loadContentTypeGroup')
                ->with($expected->id)
                ->willReturn($expected);
        }

        self::assertEquals(
            $expected,
            $this->transformer->reverseTransform($value)
        );
    }

    /**
     * @return array<string, array{mixed, \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup|null}>
     */
    public function dataProviderForReverseTransformWithValidInput(): array
    {
        $contentTypeGroup = new ContentTypeGroup([
            'id' => self::EXAMPLE_CONTENT_TYPE_GROUP_ID,
        ]);

        return [
            'integer' => [
                self::EXAMPLE_CONTENT_TYPE_GROUP_ID,
                $contentTypeGroup,
            ],
            'string' => [
                (string)self::EXAMPLE_CONTENT_TYPE_GROUP_ID,
                $contentTypeGroup,
            ],
            'null' => [null, null],
        ];
    }

    /**
     * @dataProvider dataProviderForReverseTransformWithInvalidInput
     */
    public function testReverseTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $this->transformer->reverseTransform($value);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function dataProviderForReverseTransformWithInvalidInput(): array
    {
        return [
            'string' => ['string'],
            'bool' => [true],
            'array' => [['element']],
            'object' => [new stdClass()],
        ];
    }

    public function testReverseTransformWithNotFoundException(): void
    {
        $expectedExceptionMessage = APIContentTypeGroup::class . ' not found';

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $exception = new class($expectedExceptionMessage) extends NotFoundException {
        };

        $this->contentService
            ->method('loadContentTypeGroup')
            ->with(self::EXAMPLE_CONTENT_TYPE_GROUP_ID)
            ->willThrowException($exception);

        $this->transformer->reverseTransform(self::EXAMPLE_CONTENT_TYPE_GROUP_ID);
    }
}
