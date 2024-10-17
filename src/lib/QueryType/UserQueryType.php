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
        $resolver->setDefined('exclude_users_ids');
        $resolver->setDefined('exclude_paths');
        $resolver->setDefined('section_identifiers');

        $resolver->setAllowedTypes('phrase', ['string', 'null']);
        $resolver->setAllowedTypes('exclude_users_ids', ['array']);
        $resolver->setAllowedTypes('exclude_paths', ['array']);
        $resolver->setAllowedTypes('section_identifiers', ['array']);

        $resolver->setDefaults(
            [
                'phrase' => null,
                'exclude_users_ids' => [],
                'exclude_paths' => [],
                'section_identifiers' => [],
            ],
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
    protected function getQueryFilter(array $parameters): Criterion
    {
        $userContentTypeIdentifiers = $this->getUserContentTypeIdentifiers();
        $criteria = [new Criterion\ContentTypeIdentifier($userContentTypeIdentifiers)];

        if (!empty($parameters['exclude_users_ids'])) {
            $excludedUsersIds = new Criterion\ContentId($parameters['exclude_users_ids']);
            $criteria[] = new Criterion\LogicalNot($excludedUsersIds);
        }

        if (!empty($parameters['exclude_paths'])) {
            $excludedParentLocationIds = new Criterion\Subtree($parameters['exclude_paths']);
            $criteria[] = new Criterion\LogicalNot($excludedParentLocationIds);
        }

        if (!empty($parameters['section_identifiers'])) {
            $criteria[] = new Criterion\SectionIdentifier($parameters['section_identifiers']);
        }

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

    /**
     * @return array<string>
     */
    private function getUserContentTypeIdentifiers(): array
    {
        return $this->configResolver->getParameter('user_content_type_identifier');
    }
}
