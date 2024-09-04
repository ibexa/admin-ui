<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ControllerArgumentResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @phpstan-import-type TCriterionProcessor from \Ibexa\AdminUi\REST\Input\Parser\CriterionProcessor
 */
final class ContentTreeChildrenQueryArgumentResolver implements ArgumentValueResolverInterface
{
    /** @phpstan-var TCriterionProcessor */
    private CriterionProcessorInterface $criterionProcessor;

    /**
     * @phpstan-param TCriterionProcessor $criterionProcessor
     */
    public function __construct(
        CriterionProcessorInterface $criterionProcessor
    ) {
        $this->criterionProcessor = $criterionProcessor;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Criterion::class === $argument->getType()
            && 'filter' === $argument->getName();
    }

    /**
     * @return iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion|null>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $criteria = $this->processFilterQueryCriteria($request);
        if ($argument->isNullable() && empty($criteria)) {
            yield null;

            return;
        }

        yield new LogicalAnd($criteria);
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion>
     */
    private function processFilterQueryCriteria(Request $request): array
    {
        if (!$request->query->has('filter')) {
            return [];
        }

        /** @var array<string, array<mixed>> $criteriaData */
        $criteriaData = $request->query->all('filter');
        if (empty($criteriaData)) {
            return [];
        }

        $criteria = $this->criterionProcessor->processCriteria($criteriaData);

        return iterator_to_array($criteria);
    }
}
