<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ControllerArgumentResolver;

use Ibexa\AdminUi\Exception\ValidationFailedException;
use Ibexa\AdminUi\REST\Value\UniversalDiscovery\RequestQuery;
use Ibexa\AdminUi\Validator\Builder\REST\UniversalDiscoveryRequestValidatorBuilder;
use Ibexa\Contracts\AdminUi\UniversalDiscovery\Provider;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UniversalDiscoveryRequestQueryArgumentResolver implements ArgumentValueResolverInterface
{
    private Provider $provider;

    private ValidatorInterface $validator;

    public function __construct(
        Provider $provider,
        ValidatorInterface $validator
    ) {
        $this->provider = $provider;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return RequestQuery::class === $argument->getType()
            && 'requestQuery' === $argument->getName();
    }

    /**
     * @return iterable<\Ibexa\AdminUi\REST\Value\UniversalDiscovery\RequestQuery>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $this->validate($request);

        $query = $request->query;

        yield new RequestQuery(
            $request->attributes->getInt('locationId'),
            $query->getInt('offset'),
            $query->getInt('limit', 25),
            $this->getSortClause($query),
            $query->getInt('rootLocationId', Provider::ROOT_LOCATION_ID)
        );
    }

    private function validate(Request $request): void
    {
        $validatorBuilder = new UniversalDiscoveryRequestValidatorBuilder($this->validator);
        $validatorBuilder->validateLocationId($request);

        $errors = $validatorBuilder->build()->getViolations();
        if ($errors->count() > 0) {
            throw new ValidationFailedException('request', $errors);
        }
    }

    private function getSortClause(ParameterBag $query): Query\SortClause
    {
        return $this->provider->getSortClause(
            $query->getAlpha('sortClause', Provider::SORT_CLAUSE_DATE_PUBLISHED),
            $query->getAlpha('sortOrder', Query::SORT_ASC)
        );
    }
}
