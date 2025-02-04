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
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserQueryType extends AbstractQueryType
{
    private const USER_SEARCH_PHRASE_PATTERN = '/[^\p{L}0-9@._-]/u';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefined('phrase');
        $resolver->setAllowedTypes('phrase', ['string', 'null']);

        $resolver->setDefined('extra_criteria');
        $resolver->setAllowedTypes('extra_criteria', [Query\CriterionInterface::class, 'null']);

        $resolver->setDefaults(
            [
                'phrase' => null,
                'extra_criteria' => null,
            ]
        );
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
    protected function getQueryFilter(array $parameters): Query\CriterionInterface
    {
        $criteria = [new Criterion\IsUserEnabled()];

        if (!empty($parameters['extra_criteria'])) {
            $criteria[] = $parameters['extra_criteria'];
        }

        if (!empty($parameters['phrase'])) {
            $phrase = $this->cleanSearchPhrase($parameters['phrase']);
            $criteria[] = new Criterion\LogicalOr(
                [
                    new Criterion\ContentName('*' . $phrase . '*'),
                    // Used with EQ operator and without wildcards due to hashing email in solr and elasticsearch
                    new Criterion\UserEmail($phrase, Criterion\Operator::EQ),
                ]
            );
        }

        return new Criterion\LogicalAnd($criteria);
    }

    public static function getName(): string
    {
        return 'IbexaAdminUi:User';
    }

    private function cleanSearchPhrase(string $phrase): string
    {
        $sanitizedPhrase = preg_replace(self::USER_SEARCH_PHRASE_PATTERN, '', $phrase);
        if (null === $sanitizedPhrase) {
            throw new RuntimeException('Could not sanitize search phrase.');
        }

        return $sanitizedPhrase;
    }
}
