<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentInfoValueResolver;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentInfoValueResolverTest extends TestCase
{
    private ContentInfoValueResolver $resolver;

    private MockObject&ContentService $contentServiceMock;

    protected function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->resolver = new ContentInfoValueResolver($this->contentServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentInfo::class);

        $request = new Request([], [], [
            ContentInfoValueResolver::ATTRIBUTE_CONTENT_INFO_ID => '1',
        ]);

        $mockContentInfo = $this->createMock(ContentInfo::class);

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContentInfo')
            ->with(1)
            ->willReturn($mockContentInfo);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockContentInfo], $result);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentInfo::class);

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([], $result, $expectedMessage);
    }

    /**
     * @phpstan-return array<array{attributes: array<string, mixed>, expectedMessage: string}>
     */
    public function invalidAttributesProvider(): array
    {
        return [
            'missing contentInfoId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because contentInfoId is missing',
            ],
            'invalid contentInfoId type' => [
                'attributes' => ['contentInfoId' => 'invalid'],
                'expectedMessage' => 'Should return empty because contentInfoId is invalid',
            ],
            'empty contentInfoId' => [
                'attributes' => ['contentInfoId' => ''],
                'expectedMessage' => 'Should return empty because contentInfoId is empty',
            ],
        ];
    }
}
