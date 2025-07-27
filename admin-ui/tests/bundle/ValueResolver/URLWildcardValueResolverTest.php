<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\URLWildcardValueResolver;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class URLWildcardValueResolverTest extends TestCase
{
    private URLWildcardValueResolver $resolver;

    private MockObject&URLWildcardService $urlWildcardServiceMock;

    protected function setUp(): void
    {
        $this->urlWildcardServiceMock = $this->createMock(URLWildcardService::class);
        $this->resolver = new URLWildcardValueResolver($this->urlWildcardServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(URLWildcard::class);

        $request = new Request([], [], [
            'urlWildcardId' => '123',
        ]);

        $mockURLWildcard = $this->createMock(URLWildcard::class);

        $this->urlWildcardServiceMock
            ->expects(self::once())
            ->method('load')
            ->with(123)
            ->willReturn($mockURLWildcard);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockURLWildcard], $result);
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
            ->willReturn(URLWildcard::class);

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
            'missing urlWildcardId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because urlWildcardId is missing',
            ],
            'invalid urlWildcardId type' => [
                'attributes' => ['urlWildcardId' => 'invalid'],
                'expectedMessage' => 'Should return empty because urlWildcardId is invalid',
            ],
            'empty urlWildcardId' => [
                'attributes' => ['urlWildcardId' => ''],
                'expectedMessage' => 'Should return empty because urlWildcardId is empty',
            ],
        ];
    }
}
