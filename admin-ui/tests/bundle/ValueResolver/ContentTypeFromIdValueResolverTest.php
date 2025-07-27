<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentTypeFromIdValueResolver;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentTypeFromIdValueResolverTest extends TestCase
{
    private ContentTypeFromIdValueResolver $resolver;

    private MockObject&ContentTypeService $contentTypeServiceMock;

    protected function setUp(): void
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->resolver = new ContentTypeFromIdValueResolver($this->contentTypeServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentType::class);

        $request = new Request([], [], [
            'contentTypeId' => '123',
        ]);

        $mockContentType = $this->createMock(ContentType::class);

        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentType')
            ->with(123)
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
            'missing contentTypeId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because contentTypeId is missing',
            ],
            'invalid contentTypeId type' => [
                'attributes' => ['contentTypeId' => 'invalid'],
                'expectedMessage' => 'Should return empty because contentTypeId is invalid',
            ],
            'empty contentTypeId' => [
                'attributes' => ['contentTypeId' => ''],
                'expectedMessage' => 'Should return empty because contentTypeId is empty',
            ],
        ];
    }
}
