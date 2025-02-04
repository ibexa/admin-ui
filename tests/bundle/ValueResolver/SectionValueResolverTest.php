<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ValueResolver;

use Ibexa\Bundle\AdminUi\ValueResolver\SectionValueResolver;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SectionValueResolverTest extends TestCase
{
    private SectionValueResolver $resolver;

    private MockObject&SectionService $sectionService;

    protected function setUp(): void
    {
        $this->sectionService = $this->createMock(SectionService::class);
        $this->resolver = new SectionValueResolver($this->sectionService);
    }

    public function testResolve(): void
    {
        $section = $this->createMock(Section::class);
        $attributes = ['sectionId' => '123'];

        $this->sectionService->expects(self::once())
            ->method('loadSection')
            ->with(123)
            ->willReturn($section);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(Section::class);
        $argumentMetadata->method('getName')
            ->willReturn('section');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertCount(1, $result);
        self::assertSame($section, $result[0]);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @param array<string, mixed> $attributes
     */
    public function testResolveInvalidAttributes(array $attributes, string $expectedMessage): void
    {
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $argumentMetadata->method('getType')
            ->willReturn(Section::class);
        $argumentMetadata->method('getName')
            ->willReturn('section');

        $request = new Request([], [], $attributes);

        $result = iterator_to_array($this->resolver->resolve($request, $argumentMetadata));

        self::assertSame([], $result, $expectedMessage);
    }

    /**
     * @return array<string, array{attributes: array<string, mixed>, expectedMessage: string}>
     */
    public static function invalidAttributesProvider(): array
    {
        return [
            'missing sectionId' => [
                'attributes' => [],
                'expectedMessage' => 'Should return empty because sectionId is missing',
            ],
            'invalid sectionId type' => [
                'attributes' => ['sectionId' => 'invalid'],
                'expectedMessage' => 'Should return empty because sectionId is not numeric',
            ],
        ];
    }
}
