<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ObjectStateGroupValueResolver;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ObjectStateGroupValueResolverTest extends TestCase
{
    private ObjectStateGroupValueResolver $resolver;

    private MockObject&ObjectStateService $objectStateServiceMock;

    protected function setUp(): void
    {
        $this->objectStateServiceMock = $this->createMock(ObjectStateService::class);
        $this->resolver = new ObjectStateGroupValueResolver($this->objectStateServiceMock);
    }

    public function testResolve(): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ObjectStateGroup::class);

        $request = new Request([], [], [
            'objectStateGroupId' => '123',
        ]);

        $mockObjectStateGroup = $this->createMock(ObjectStateGroup::class);

        $this->objectStateServiceMock
            ->expects(self::once())
            ->method('loadObjectStateGroup')
            ->with(123)
            ->willReturn($mockObjectStateGroup);

        $result = iterator_to_array($this->resolver->resolve($request, $mockArgumentMetadata));

        self::assertSame([$mockObjectStateGroup], $result);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     * @param string $expectedMessage
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $mockArgumentMetadata = $this->createMock(ArgumentMetadata::class);
        $mockArgumentMetadata->expects(self::once())
            ->method('getType')
            ->willReturn(ObjectStateGroup::class);

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
            'missing objectStateGroupId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because objectStateGroupId is missing',
            ],
            'invalid objectStateGroupId type' => [
                'attributes' => ['objectStateGroupId' => 'invalid'],
                'expectedMessage' => 'Should return empty because objectStateGroupId is invalid',
            ],
            'empty objectStateGroupId' => [
                'attributes' => ['objectStateGroupId' => ''],
                'expectedMessage' => 'Should return empty because objectStateGroupId is empty',
            ],
        ];
    }
}
