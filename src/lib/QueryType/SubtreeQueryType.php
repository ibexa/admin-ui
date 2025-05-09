<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\QueryType;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\QueryType\OptionsResolverBasedQueryType;
use Ibexa\Core\QueryType\QueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class SubtreeQueryType extends OptionsResolverBasedQueryType implements QueryType
{
    protected const OWNED_OPTION_NAME = 'owned';
    protected const SUBTREE_OPTION_NAME = 'subtree';

    protected ConfigResolverInterface $configResolver;

    private PermissionResolver $permissionResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        PermissionResolver $permissionResolver
    ) {
        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
    }

    public function doGetQuery(array $parameters): Query
    {
        $subtreeCriterion = new Query\Criterion\Subtree($parameters[SubtreeQueryType::SUBTREE_OPTION_NAME]);

        $ownerId = $parameters[SubtreeQueryType::OWNED_OPTION_NAME]
            ? $this->permissionResolver->getCurrentUserReference()->getUserId()
            : null;

        $filter = null;

        if (null !== $ownerId) {
            $filter = new Query\Criterion\LogicalAnd([
                $subtreeCriterion,
                new Query\Criterion\UserMetadata(
                    Query\Criterion\UserMetadata::OWNER,
                    Query\Criterion\Operator::EQ,
                    $ownerId
                ),
            ]);
        } else {
            $filter = $subtreeCriterion;
        }

        return new Query([
            'filter' => $filter,
            'sortClauses' => [new Query\SortClause\DateModified(Query::SORT_DESC)],
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([self::SUBTREE_OPTION_NAME, self::OWNED_OPTION_NAME]);
        $resolver->setAllowedTypes(self::SUBTREE_OPTION_NAME, 'string');
        $resolver->setAllowedTypes(self::OWNED_OPTION_NAME, 'bool');
        $resolver->setDefault(self::SUBTREE_OPTION_NAME, $this->getSubtreePathFromConfiguration());
        $resolver->setDefault(self::OWNED_OPTION_NAME, false);
    }

    abstract protected function getSubtreePathFromConfiguration(): string;
}
