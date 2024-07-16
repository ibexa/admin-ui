<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\ControllerArgumentResolver;

use Ibexa\AdminUi\REST\Input\Parser\CriterionProcessorInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ContentTreeChildrenQueryArgumentResolver implements ArgumentValueResolverInterface
{
    private CriterionProcessorInterface $criterionProcessor;

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
     * @return iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield new LogicalAnd($this->processFilterQueryCriteria($request));
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

        return iterator_to_array($this->criterionProcessor->processCriteria($criteriaData));
    }
}
