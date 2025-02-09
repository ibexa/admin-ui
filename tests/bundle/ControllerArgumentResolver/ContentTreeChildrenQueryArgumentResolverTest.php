<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ControllerArgumentResolver;

use ArrayIterator;
use Generator;
use Ibexa\Bundle\AdminUi\ValueResolver\ContentTreeChildrenQueryValueResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Traversable;

/**
 * @phpstan-type TCriterionProcessor \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface<
 *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
 * >
 *
 * @covers \Ibexa\Bundle\AdminUi\ValueResolver\ContentTreeChildrenQueryValueResolver
 */
final class ContentTreeChildrenQueryArgumentResolverTest extends TestCase
{
    private ValueResolverInterface $resolver;

    /** @phpstan-var TCriterionProcessor&\PHPUnit\Framework\MockObject\MockObject */
    private CriterionProcessorInterface $criterionProcessor;

    protected function setUp(): void
    {
        $this->criterionProcessor = $this->createMock(CriterionProcessorInterface::class);
        $this->resolver = new ContentTreeChildrenQueryValueResolver(
            $this->criterionProcessor
        );
    }

    /**
     * @dataProvider provideDataForUnsupported
     */
    public function testUnsupported(ArgumentMetadata $argumentMetadata): void
    {
        $actualResult = $this->resolver->resolve(
            new Request(),
            $argumentMetadata
        );

        self::assertEmpty(iterator_to_array($actualResult));
    }

    /**
     * @return iterable<string, array{
     *     \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata
     * }>
     */
    public function provideDataForUnsupported(): iterable
    {
        yield 'Not supported' => [
            $this->createMock(ArgumentMetadata::class),
        ];

        yield 'Not supported - invalid argument type' => [
            $this->createArgumentMetadata(
                'filter',
                'foo',
            ),
        ];

        yield 'Not supported - invalid argument name' => [
            $this->createArgumentMetadata(
                'foo',
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
        CriterionInterface $expected,
        Request $request,
        Traversable $expectedCriteria,
        array $criteriaToProcess = []
    ): void {
        if (!empty($criteriaToProcess)) {
            $this->mockCriterionProcessorProcessCriteria($criteriaToProcess, $expectedCriteria);
        }

        $generator = $this->resolver->resolve(
            $request,
            $this->createArgumentMetadata(
                'filter',
                Criterion::class
            )
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
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface,
     *     \Symfony\Component\HttpFoundation\Request,
     *     \Traversable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface>,
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
