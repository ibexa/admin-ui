<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\VersionInfoValueResolver;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class VersionInfoValueResolverTest extends TestCase
{
    private VersionInfoValueResolver $resolver;

    private MockObject&ContentService $contentServiceMock;

    protected function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->resolver = new VersionInfoValueResolver($this->contentServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(VersionInfo::class);

        $request = new Request([], [], [
            'versionNo' => '1',
            'contentId' => '123',
        ]);

        $mockContentInfo = $this->createMock(ContentInfo::class);
        $mockVersionInfo = $this->createMock(VersionInfo::class);

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContentInfo')
            ->with(123)
            ->willReturn($mockContentInfo);

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadVersionInfo')
            ->with($mockContentInfo, 1)
            ->willReturn($mockVersionInfo);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockVersionInfo], $result);
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
            ->willReturn(VersionInfo::class);

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
            'missing versionNo' => [
                'attributes' => ['contentId' => '123'],
                'expectedMessage' => 'Should return empty because versionNo is missing',
            ],
            'missing contentId' => [
                'attributes' => ['versionNo' => '1'],
                'expectedMessage' => 'Should return empty because contentId is missing',
            ],
            'missing both attributes' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because both versionNo and contentId are missing',
            ],
            'invalid contentId type' => [
                'attributes' => ['versionNo' => '1', 'contentId' => 'invalid'],
                'expectedMessage' => 'Should return empty because contentId is invalid',
            ],
            'invalid versionNo type' => [
                'attributes' => ['versionNo' => 'invalid', 'contentId' => '123'],
                'expectedMessage' => 'Should return empty because versionNo is invalid',
            ],
            'invalid attributes type' => [
                'attributes' => ['versionNo' => 1, 'contentId' => 123],
                'expectedMessage' => 'Should return empty because attributes are integers instead of strings',
            ],
        ];
    }
}
