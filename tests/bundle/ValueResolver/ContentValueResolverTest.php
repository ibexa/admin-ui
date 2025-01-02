<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentValueResolver;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentValueResolverTest extends TestCase
{
    private ContentValueResolver $resolver;

    private MockObject&ContentService $contentServiceMock;

    protected function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->resolver = new ContentValueResolver($this->contentServiceMock);
    }

    /**
     * @dataProvider validAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolve(array $attributes): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(Content::class);

        $request = new Request([], [], $attributes);
        $mockContent = $this->createMock(Content::class);

        $this->contentServiceMock
            ->expects(self::once())
            ->method('loadContent')
            ->with(1, ['eng-GB'], $attributes['versionNo'] ?? null)
            ->willReturn($mockContent);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockContent], $result);
    }

    /**
     * @phpstan-return array<array{attributes: array<string, mixed>}>
     */
    public function validAttributesProvider(): array
    {
        return [
            'full valid attributes' => [
                'attributes' => [
                    'contentId' => '1',
                    'versionNo' => '2',
                    'languageCode' => ['eng-GB'],
                ],
            ],
            'missing versionNo' => [
                'attributes' => [
                    'contentId' => '1',
                    'languageCode' => ['eng-GB'],
                ],
            ],
        ];
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
            ->willReturn(Content::class);

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
            'missing contentId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because contentId is missing',
            ],
            'invalid contentId type' => [
                'attributes' => ['contentId' => 'invalid', 'versionNo' => '1', 'languageCode' => ['en']],
                'expectedMessage' => 'Should return empty because contentId is invalid',
            ],
            'invalid versionNo type' => [
                'attributes' => ['contentId' => '1', 'versionNo' => 'invalid', 'languageCode' => ['en']],
                'expectedMessage' => 'Should return empty because versionNo is invalid',
            ],
            'invalid languageCode type' => [
                'attributes' => ['contentId' => '1', 'versionNo' => '1', 'languageCode' => 'eng-GB'],
                'expectedMessage' => 'Should return empty because languageCode is invalid',
            ],
        ];
    }
}
