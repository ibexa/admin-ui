<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Pagination\Pagerfanta\RelationAdapter;
use Ibexa\AdminUi\Pagination\Pagerfanta\ReverseRelationAdapter;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class RelationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-relations';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        protected readonly PermissionResolver $permissionResolver,
        protected readonly DatasetFactory $datasetFactory,
        protected readonly ContentTypeService $contentTypeService,
        EventDispatcherInterface $eventDispatcher,
        private readonly ContentService $contentService
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'relations';
    }

    public function getName(): string
    {
        /** @Desc("Relations") */
        return $this->translator->trans('tab.name.relations', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 600;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        return $this->permissionResolver->canUser(
            'content',
            'reverserelatedlist',
            $parameters['content']
        );
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/relations/tab.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        $reverseRelationPaginationParams = $contextParameters['reverse_relation_pagination_params'];
        $reverseRelationPagination = new Pagerfanta(
            new ReverseRelationAdapter($this->contentService, $this->datasetFactory, $content)
        );
        $reverseRelationPagination->setMaxPerPage($reverseRelationPaginationParams['limit']);
        $reverseRelationPagination->setCurrentPage(min(
            max($reverseRelationPaginationParams['page'], 1),
            $reverseRelationPagination->getNbPages()
        ));

        $contentTypeIds = [];
        $relationPagination = new Pagerfanta(
            new RelationAdapter($this->contentService, $this->datasetFactory, $content)
        );
        $relationPaginationParams = $contextParameters['relation_pagination_params'];
        $relationPagination->setMaxPerPage($relationPaginationParams['limit']);
        $relationPagination->setCurrentPage(min(
            max($relationPaginationParams['page'], 1),
            $relationPagination->getNbPages()
        ));

        $viewParameters = [];
        $relations = $relationPagination->getCurrentPageResults();
        foreach ($relations as $relation) {
            if ($relation->isAccessible()) {
                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation */
                $contentTypeIds[] = $relation->getDestinationContentInfo()->contentTypeId;
            }
        }
        $viewParameters['relation_pager'] = $relationPagination;
        $viewParameters['relation_pagination_params'] = $relationPaginationParams;

        if ($this->permissionResolver->canUser('content', 'reverserelatedlist', $content)) {
            $reverseRelations = $reverseRelationPagination->getCurrentPageResults();

            foreach ($reverseRelations as $relation) {
                if ($relation->isAccessible()) {
                    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation */
                    $contentTypeIds[] = $relation->getSourceContentInfo()->contentTypeId;
                }
            }

            $viewParameters['reverse_relation_pager'] = $reverseRelationPagination;
            $viewParameters['reverse_relation_pagination_params'] = $reverseRelationPaginationParams;
        }

        if (!empty($contentTypeIds)) {
            $viewParameters['content_types'] = $this->contentTypeService->loadContentTypeList(
                array_unique($contentTypeIds)
            );
        } else {
            $viewParameters['content_types'] = [];
        }

        return array_replace($contextParameters, $viewParameters);
    }
}
