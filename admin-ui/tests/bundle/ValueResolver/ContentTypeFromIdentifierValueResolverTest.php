<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentTypeFromIdentifierValueResolver;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentTypeFromIdentifierValueResolverTest extends TestCase
{
    private ContentTypeFromIdentifierValueResolver $resolver;

    private MockObject&ContentTypeService $contentTypeServiceMock;

    protected function setUp(): void
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->resolver = new ContentTypeFromIdentifierValueResolver($this->contentTypeServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentType::class);

        $request = new Request([], [], [
            'contentTypeIdentifier' => 'article',
        ]);

        $mockContentType = $this->createMock(ContentType::class);

        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with('article')
            ->willReturn($mockContentType);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockContentType], $result);
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
            ->willReturn(ContentType::class);

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
            'missing contentTypeIdentifier' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because contentTypeIdentifier is missing',
            ],
            'invalid contentTypeIdentifier type' => [
                'attributes' => ['contentTypeIdentifier' => 123], // Adjusted invalid type
                'expectedMessage' => 'Should return empty because contentTypeIdentifier is invalid',
            ],
            'empty contentTypeIdentifier' => [
                'attributes' => ['contentTypeIdentifier' => ''], // Adjusted empty value
                'expectedMessage' => 'Should return empty because contentTypeIdentifier is empty',
            ],
        ];
    }
}
