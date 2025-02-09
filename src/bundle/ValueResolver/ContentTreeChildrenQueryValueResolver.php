<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @phpstan-import-type TCriterionProcessor from \Ibexa\AdminUi\REST\Input\Parser\CriterionProcessor
 */
final class ContentTreeChildrenQueryValueResolver implements ValueResolverInterface
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

    /**
     * @return iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface|null>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) {
            return [];
        }

        $criteria = $this->processFilterQueryCriteria($request);
        if ($argument->isNullable() && empty($criteria)) {
            return [null];
        }

        yield new LogicalAnd($criteria);
    }

    private function supports(ArgumentMetadata $argument): bool
    {
        if ($argument->getType() === null) {
            return false;
        }

        return is_a($argument->getType(), CriterionInterface::class, true)
            && 'filter' === $argument->getName();
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface>
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
