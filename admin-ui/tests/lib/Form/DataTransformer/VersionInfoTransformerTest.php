<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\VersionInfoTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @phpstan-type TTransformedValue array{content_info: \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo, version_no: int}
 */
final class VersionInfoTransformerTest extends TestCase
{
    private const EXAMPLE_CONTENT_ID = 123456;
    private const EXAMPLE_VERSION_NO = 7;

    private ContentService&MockObject $contentService;

    private VersionInfoTransformer $transformer;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->transformer = new VersionInfoTransformer($this->contentService);
    }

    /**
     * @dataProvider dataProviderForTransformWithValidInput
     *
     * @phpstan-param TTransformedValue|null $expected
     */
    public function testTransformWithValidInput(?VersionInfo $value, ?array $expected): void
    {
        self::assertEquals(
            $expected,
            $this->transformer->transform($value)
        );
    }

    /**
     * @phpstan-return list<array{\Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null, TTransformedValue|null, }>
     */
    public function dataProviderForTransformWithValidInput(): array
    {
        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        $versionInfo = $this->createVersionInfoMock($contentInfo, self::EXAMPLE_VERSION_NO);

        return [
            [null, null],
            [
                $versionInfo,
                [
                    'content_info' => $contentInfo,
                    'version_no' => self::EXAMPLE_VERSION_NO,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTransformWithInvalidInput
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Value cannot be transformed because the passed value is not a VersionInfo object');

        $this->transformer->transform($value);
    }

    /**
     * @phpstan-return array<string, array{mixed}>
     */
    public function dataProviderForTransformWithInvalidInput(): array
    {
        $object = new class() {
        };

        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [$object],
        ];
    }

    /**
     * @dataProvider dataProviderForReverseTransformWithValidInput
     *
     * @phpstan-param array{
     *      content_info: \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null,
     *      version_no: int|string|null
     * }|null $value
     */
    public function testReverseTransformWithValidInput(?array $value, ?VersionInfo $expected): void
    {
        if ($expected !== null && $value !== null) {
            $this->contentService
                ->expects(self::once())
                ->method('loadVersionInfo')
                ->with(
                    self::equalTo($value['content_info']),
                    self::logicalAnd(
                        self::equalTo($value['version_no']),
                        // Make sure value is cast to int
                        self::isType('int')
                    )
                )
                ->willReturn($expected);
        }

        self::assertEquals(
            $expected,
            $this->transformer->reverseTransform($value)
        );
    }

    /**
     * @phpstan-return array<string, array{
     *     array{
     *          content_info: \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null,
     *          version_no: int|string|null
     *     }|null,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null
     * }>
     */
    public function dataProviderForReverseTransformWithValidInput(): array
    {
        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        $versionInfo = $this->createVersionInfoMock($contentInfo, self::EXAMPLE_VERSION_NO);

        return [
            'null' => [null, null],
            'empty' => [
                [
                    'content_info' => null,
                    'version_no' => null,
                ],
                null,
            ],
            'non_empty' => [
                [
                    'content_info' => $contentInfo,
                    'version_no' => self::EXAMPLE_VERSION_NO,
                ],
                $versionInfo,
            ],
            'non_empty_with_version_cast' => [
                [
                    'content_info' => $contentInfo,
                    'version_no' => (string)self::EXAMPLE_VERSION_NO,
                ],
                $versionInfo,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForReverseTransformWithInvalidInput
     *
     * @phpstan-param array{} $value
     */
    public function testReverseTransformWithInvalidInput(array $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage("Invalid data. Value array is missing 'content_info' and/or 'version_no' keys");

        $this->transformer->reverseTransform($value);
    }

    /**
     * @phpstan-return array<string, array{array{}}>
     */
    public function dataProviderForReverseTransformWithInvalidInput(): array
    {
        return [
            'empty_array' => [
                [],
            ],
        ];
    }

    public function testReverseTransformForNonExistingVersionInfo(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('VersionInfo not found');

        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        $value = [
            'content_info' => $contentInfo,
            'version_no' => self::EXAMPLE_VERSION_NO,
        ];

        $exception = new class('VersionInfo not found') extends NotFoundException {
        };

        $this->contentService
            ->method('loadVersionInfo')
            ->with($contentInfo, self::EXAMPLE_VERSION_NO)
            ->willThrowException($exception);

        $this->transformer->reverseTransform($value);
    }

    public function testReverseTransformForUnauthorizedVersionInfo(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unauthorized VersionInfo');

        $contentInfo = new ContentInfo([
            'id' => self::EXAMPLE_CONTENT_ID,
        ]);

        $value = [
            'content_info' => $contentInfo,
            'version_no' => self::EXAMPLE_VERSION_NO,
        ];

        $exception = new class('Unauthorized VersionInfo') extends UnauthorizedException {
        };

        $this->contentService
            ->method('loadVersionInfo')
            ->with($contentInfo, self::EXAMPLE_VERSION_NO)
            ->willThrowException($exception);

        $this->transformer->reverseTransform($value);
    }

    private function createVersionInfoMock(ContentInfo $contentInfo, int $versionNo): VersionInfo
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo
            ->method('__get')
            ->willReturnMap([
                ['versionNo', $versionNo],
            ]);
        $versionInfo->method('getContentInfo')->willReturn($contentInfo);

        return $versionInfo;
    }
}
