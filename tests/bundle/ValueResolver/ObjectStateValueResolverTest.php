<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\ObjectStateValueResolver;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ObjectStateValueResolverTest extends TestCase
{
    private ObjectStateValueResolver $resolver;

    private MockObject&ObjectStateService $objectStateService;

    protected function setUp(): void
    {
        $this->objectStateService = $this->createMock(ObjectStateService::class);
        $this->resolver = new ObjectStateValueResolver($this->objectStateService);
    }

    public function testResolve(): void
    {
        $request = new Request([], [], [
            'objectStateId' => '123',
        ]);

        $objectState = $this->createMock(ObjectState::class);

        $this->objectStateService
            ->method('loadObjectState')
            ->with(123)
            ->willReturn($objectState);

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(ObjectState::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertSame($objectState, $result[0]);
    }

    /**
     * @dataProvider invalidRequestProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidRequest(array $attributes): void
    {
        $request = new Request([], [], $attributes);

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(ObjectState::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(0, $result);
    }

    /**
     * @phpstan-return array<string, array<int, array<string, mixed>>>
     */
    public function invalidRequestProvider(): array
    {
        return [
            'missing objectStateId' => [
                [],
            ],
            'invalid objectStateId type' => [
                ['objectStateId' => 'invalid'],
            ],
        ];
    }
}
