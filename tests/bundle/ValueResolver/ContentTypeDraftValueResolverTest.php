<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ContentTypeDraftValueResolver;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentTypeDraftValueResolverTest extends TestCase
{
    private ContentTypeDraftValueResolver $resolver;

    private MockObject&ContentTypeService $contentTypeServiceMock;

    protected function setUp(): void
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->resolver = new ContentTypeDraftValueResolver($this->contentTypeServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ContentTypeDraft::class);

        $request = new Request([], [], [
            'contentTypeId' => '1',
        ]);

        $mockContentTypeDraft = $this->createMock(ContentTypeDraft::class);

        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentTypeDraft')
            ->with(1)
            ->willReturn($mockContentTypeDraft);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockContentTypeDraft], $result);
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
            ->willReturn(ContentTypeDraft::class);

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
