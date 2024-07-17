<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\ControllerArgumentResolver;

use ArrayIterator;
use Generator;
use Ibexa\Bundle\AdminUi\ControllerArgumentResolver\ContentTreeChildrenQueryArgumentResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Traversable;

/**
 * @phpstan-type TCriterionProcessor \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface<
 *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
 * >
 *
 * @covers \Ibexa\Bundle\AdminUi\ControllerArgumentResolver\ContentTreeChildrenQueryArgumentResolver
 */
final class ContentTreeChildrenQueryArgumentResolverTest extends TestCase
{
    private ArgumentValueResolverInterface $resolver;

    /** @phpstan-var TCriterionProcessor&\PHPUnit\Framework\MockObject\MockObject */
    private CriterionProcessorInterface $criterionProcessor;

    protected function setUp(): void
    {
        $this->criterionProcessor = $this->createMock(CriterionProcessorInterface::class);
        $this->resolver = new ContentTreeChildrenQueryArgumentResolver(
            $this->criterionProcessor
        );
    }

    /**
     * @dataProvider provideDataForTestSupports
     */
    public function testSupports(
        bool $expected,
        ArgumentMetadata $argumentMetadata
    ): void {
        self::assertSame(
            $expected,
            $this->resolver->supports(
                new Request(),
                $argumentMetadata
            )
        );
    }

    /**
     * @return iterable<array{
     *     bool,
     *     \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata
     * }>
     */
    public function provideDataForTestSupports(): iterable
    {
        yield 'Not supported' => [
            false,
            $this->createMock(ArgumentMetadata::class),
        ];

        yield 'Not supported - Invalid argument type' => [
            false,
            $this->createArgumentMetadata(
                'filter',
                'foo',
            ),
        ];

        yield 'Not supported - Invalid argument name' => [
            false,
            $this->createArgumentMetadata(
                'foo',
                Criterion::class,
            ),
        ];

        yield 'Supported' => [
            true,
            $this->createArgumentMetadata(
                'filter',
                Criterion::class,
            ),
        ];
    }

    /**
     * @dataProvider provideDataForTestResolve
     *
     * @param array<string, string|array<mixed>> $criteriaToProcess
     * @param Traversable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface> $expectedCriteria
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testResolve(
        Criterion $expected,
        Request $request,
        Traversable $expectedCriteria,
        array $criteriaToProcess = []
    ): void {
        if (!empty($criteriaToProcess)) {
            $this->mockCriterionProcessorProcessCriteria($criteriaToProcess, $expectedCriteria);
        }

        $generator = $this->resolver->resolve(
            $request,
            $this->createMock(ArgumentMetadata::class)
        );

        self::assertInstanceOf(Generator::class, $generator);
        $resolvedArguments = iterator_to_array($generator);

        self::assertCount(1, $resolvedArguments);

        self::assertEquals(
            $expected,
            $resolvedArguments[0]
        );
    }

    /**
     * @return iterable<array{
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion,
     *     \Symfony\Component\HttpFoundation\Request,
     *     \Traversable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion>,
     *     3?: array<string, string>,
     * }>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    public function provideDataForTestResolve(): iterable
    {
        yield 'Return null - missing filter query param' => [
            new LogicalAnd([]),
            $this->createRequest(null),
            new ArrayIterator(),
        ];

        yield 'Return null - empty value for filter query param' => [
            new LogicalAnd([]),
            $this->createRequest([]),
            new ArrayIterator(),
        ];

        $criteriaToProcess = [
            'ContentTypeIdentifierCriterion' => 'folder',
        ];
        $expectedCriteria = [
            new ContentTypeIdentifier('folder'),
        ];

        yield 'Return filter with ContentTypeIdentifier criterion' => [
            new LogicalAnd($expectedCriteria),
            $this->createRequest($criteriaToProcess),
            new ArrayIterator($expectedCriteria),
            $criteriaToProcess,
        ];
    }

    /**
     * @param array<string, string|array<mixed>> $criteriaToProcess
     * @param Traversable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface> $expectedCriteria
     */
    private function mockCriterionProcessorProcessCriteria(
        ?array $criteriaToProcess,
        Traversable $expectedCriteria
    ): void {
        $this->criterionProcessor
            ->method('processCriteria')
            ->with($criteriaToProcess)
            ->willReturn($expectedCriteria);
    }

    /**
     * @param array<mixed>|null $filter
     */
    private function createRequest(?array $filter): Request
    {
        $request = Request::create('/');

        if (null !== $filter) {
            $request->query->set('filter', $filter);
        }

        return $request;
    }

    private function createArgumentMetadata(
        string $name,
        string $type
    ): ArgumentMetadata {
        return new ArgumentMetadata(
            $name,
            $type,
            true,
            false,
            ''
        );
    }
}
