<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\QueryType\BuiltIn\AbstractQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserQueryType extends AbstractQueryType
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefined('phrase');
        $resolver->setAllowedTypes('phrase', ['string', 'null']);
        $resolver->setDefaults(['phrase' => null]);
    }

    /**
     * @param array<mixed> $parameters
     */
    protected function doGetQuery(array $parameters): Query
    {
        $parameters['filter']['siteaccess_aware'] = false;

        return parent::doGetQuery($parameters);
    }

    /**
     * @param array<mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    protected function getQueryFilter(array $parameters): Criterion
    {
        $criteria = [
            new Criterion\IsUserBased(),
            new Criterion\IsUserEnabled(),
        ];

        if (!empty($parameters['phrase'])) {
            $phrase = '*' . $parameters['phrase'] . '*';
            $criteria[] = new Criterion\LogicalOr(
                [
                    new Criterion\Field('first_name', Criterion\Operator::LIKE, $phrase),
                    new Criterion\Field('last_name', Criterion\Operator::LIKE, $phrase),
                    new Criterion\UserEmail($phrase, Criterion\Operator::LIKE),
                ]
            );
        }

        return new Criterion\LogicalAnd($criteria);
    }

    public static function getName(): string
    {
        return 'IbexaAdminUi:User';
    }
}
