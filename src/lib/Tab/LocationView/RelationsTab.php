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
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class RelationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-location-view-relations';

    /** @var PermissionResolver */
    protected $permissionResolver;

    /** @var DatasetFactory */
    protected $datasetFactory;

    /** @var ContentTypeService */
    protected $contentTypeService;

    /** @var ContentService */
    private $contentService;

    /**
     * @param Environment $twig
     * @param TranslatorInterface $translator
     * @param PermissionResolver $permissionResolver
     * @param DatasetFactory $datasetFactory
     * @param ContentTypeService $contentTypeService
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContentService $contentService
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PermissionResolver $permissionResolver,
        DatasetFactory $datasetFactory,
        ContentTypeService $contentTypeService,
        EventDispatcherInterface $eventDispatcher,
        ContentService $contentService
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->permissionResolver = $permissionResolver;
        $this->datasetFactory = $datasetFactory;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'relations';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("Relations") */
        return $this->translator->trans('tab.name.relations', [], 'ibexa_locationview');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 600;
    }

    /**
     * Get information about tab presence.
     *
     * @param array $parameters
     *
     * @return bool
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        return $this->permissionResolver->canUser('content', 'reverserelatedlist', $parameters['content']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/relations/tab.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var Content $content */
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
                /** @var Relation $relation */
                $contentTypeIds[] = $relation->getDestinationContentInfo()->contentTypeId;
            }
        }
        $viewParameters['relation_pager'] = $relationPagination;
        $viewParameters['relation_pagination_params'] = $relationPaginationParams;

        if ($this->permissionResolver->canUser('content', 'reverserelatedlist', $content)) {
            $reverseRelations = $reverseRelationPagination->getCurrentPageResults();

            foreach ($reverseRelations as $relation) {
                if ($relation->isAccessible()) {
                    /** @var Relation $relation */
                    $contentTypeIds[] = $relation->getSourceContentInfo()->contentTypeId;
                }
            }

            $viewParameters['reverse_relation_pager'] = $reverseRelationPagination;
            $viewParameters['reverse_relation_pagination_params'] = $reverseRelationPaginationParams;
        }

        if (!empty($contentTypeIds)) {
            $viewParameters['content_types'] = $this->contentTypeService->loadContentTypeList(array_unique($contentTypeIds));
        } else {
            $viewParameters['content_types'] = [];
        }

        return array_replace($contextParameters, $viewParameters);
    }
}

class_alias(RelationsTab::class, 'EzSystems\EzPlatformAdminUi\Tab\LocationView\RelationsTab');
