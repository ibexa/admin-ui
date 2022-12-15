<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\QueryType;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class LocationSubtreeQueryType extends OptionsResolverBasedQueryType
{
    protected const OWNED_OPTION_NAME = 'owned';
    protected const SUBTREE_OPTION_NAME = 'subtree';

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    protected $configResolver;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        PermissionResolver $permissionResolver
    ) {
        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
    }

    public function doGetQuery(array $parameters): LocationQuery
    {
        $subtreeCriterion = new Query\Criterion\Subtree($parameters[self::SUBTREE_OPTION_NAME]);

        $ownerId = $parameters[self::OWNED_OPTION_NAME]
            ? $this->permissionResolver->getCurrentUserReference()->getUserId()
            : null;

        if ($ownerId !== null) {
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

        return new LocationQuery([
            'filter' => $filter,
            'sortClauses' => [new Query\SortClause\DateModified(Query::SORT_DESC)],
        ]);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefined([self::SUBTREE_OPTION_NAME, self::OWNED_OPTION_NAME]);
        $optionsResolver->setAllowedTypes(self::SUBTREE_OPTION_NAME, 'string');
        $optionsResolver->setAllowedTypes(self::OWNED_OPTION_NAME, 'bool');
        $optionsResolver->setDefault(self::SUBTREE_OPTION_NAME, $this->getSubtreePathFromConfiguration());
        $optionsResolver->setDefault(self::OWNED_OPTION_NAME, false);
    }

    abstract protected function getSubtreePathFromConfiguration(): string;
}
