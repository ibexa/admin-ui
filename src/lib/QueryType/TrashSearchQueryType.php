<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\QueryType;

use Ibexa\AdminUi\Form\Data\Search\TrashSearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrashSearchQueryType extends OptionsResolverBasedQueryType
{
    public const FORM_FIELD_SORT_MAP = [
        'name' => SortClause\ContentName::class,
        'trashed' => SortClause\Trash\DateTrashed::class,
        'content_type' => SortClause\Trash\ContentTypeName::class,
        'section' => SortClause\SectionName::class,
        'creator' => SortClause\Trash\UserLogin::class,
    ];

    protected function doGetQuery(array $parameters): Query
    {
        /** @var \Ibexa\AdminUi\Form\Data\Search\TrashSearchData $searchData */
        $searchData = $parameters['search_data'];
        $query = new Query();

        if (empty($searchData)) {
            return $query;
        }

        $this->addCriteria($searchData, $query);
        $this->addSort($searchData, $query);

        return $query;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'search_data' => new TrashSearchData(),
        ]);

        $optionsResolver->setAllowedTypes('search_data', [TrashSearchData::class, 'null']);
    }

    public static function getName(): string
    {
        return 'IbexaAdminUi:TrashSearchQuery';
    }

    protected function addCriteria(TrashSearchData $searchData, Query $query): void
    {
        $criteria = [];

        if ($searchData->getSection() instanceof Section) {
            $criteria[] = new Criterion\SectionId($searchData->getSection()->id);
        }

        if ($searchData->getContentType() instanceof ContentType) {
            $criteria[] = new Criterion\ContentTypeId([$searchData->getContentType()->id]);
        }

        if (!empty($searchData->getTrashedInterval())) {
            $trashedInterval = $searchData->getTrashedInterval();

            $criteria[] = new Criterion\DateMetadata(
                Criterion\DateMetadata::TRASHED,
                Criterion\Operator::BETWEEN,
                [
                    $trashedInterval['start_date'],
                    $trashedInterval['end_date'],
                ]
            );
        }

        if ($searchData->getCreator() instanceof User) {
            $criteria[] = new Criterion\UserMetadata(
                Criterion\UserMetadata::OWNER,
                Criterion\Operator::EQ,
                $searchData->getCreator()->id
            );
        }

        if (!empty($criteria)) {
            $query->filter = new Criterion\LogicalAnd($criteria);
        }
    }

    private function addSort(TrashSearchData $searchData, Query $query): void
    {
        $sort = $searchData->getSort();

        if (empty($sort)) {
            return;
        }

        $sortField = $sort['field'];
        $sortDirection = $sort['direction'] == 0 ? Query::SORT_DESC : Query::SORT_ASC;

        if (array_key_exists($sortField, self::FORM_FIELD_SORT_MAP)) {
            $sortClassName = self::FORM_FIELD_SORT_MAP[$sortField];
            $query->sortClauses[] = new $sortClassName($sortDirection);
        }
    }
}
