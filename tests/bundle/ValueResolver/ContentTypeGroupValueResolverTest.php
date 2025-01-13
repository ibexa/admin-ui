<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentTypeGroupValueResolver;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentTypeGroupValueResolverTest extends TestCase
{
    private ContentTypeGroupValueResolver $resolver;

    private MockObject&ContentTypeService $contentTypeServiceMock;

    protected function setUp(): void
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->resolver = new ContentTypeGroupValueResolver($this->contentTypeServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentTypeGroup::class);

        $request = new Request([], [], [
            'contentTypeGroupId' => '123',
        ]);

        $mockContentTypeGroup = $this->createMock(ContentTypeGroup::class);

        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentTypeGroup')
            ->with(123)
            ->willReturn($mockContentTypeGroup);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockContentTypeGroup], $result);
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
            ->willReturn(ContentTypeGroup::class);

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
            'missing contentTypeGroupId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because contentTypeGroupId is missing',
            ],
            'invalid contentTypeGroupId type' => [
                'attributes' => ['contentTypeGroupId' => 'invalid'],
                'expectedMessage' => 'Should return empty because contentTypeGroupId is invalid',
            ],
            'empty contentTypeGroupId' => [
                'attributes' => ['contentTypeGroupId' => ''],
                'expectedMessage' => 'Should return empty because contentTypeGroupId is empty',
            ],
        ];
    }
}
