<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Ibexa\Core\QueryType\QueryType;
use Pagerfanta\Pagerfanta;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class EveryoneMediaTab extends AbstractTab implements OrderedTabInterface
{
    /** @var \Ibexa\AdminUi\Tab\Dashboard\PagerContentToDataMapper */
    protected $pagerContentToDataMapper;

    /** @var \Ibexa\Contracts\Core\Repository\SearchService */
    protected $searchService;

    /** @var \Ibexa\Core\QueryType\QueryType */
    private $mediaSubtreeQueryType;

    /**
     * @param \Twig\Environment $twig
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Ibexa\AdminUi\Tab\Dashboard\PagerContentToDataMapper $pagerContentToDataMapper
     * @param \Ibexa\Contracts\Core\Repository\SearchService $searchService
     * @param \Ibexa\Core\QueryType\QueryType $mediaSubtreeQueryType
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PagerContentToDataMapper $pagerContentToDataMapper,
        SearchService $searchService,
        QueryType $mediaSubtreeQueryType
    ) {
        parent::__construct($twig, $translator);

        $this->pagerContentToDataMapper = $pagerContentToDataMapper;
        $this->searchService = $searchService;
        $this->mediaSubtreeQueryType = $mediaSubtreeQueryType;
    }

    public function getIdentifier(): string
    {
        return 'everyone-media';
    }

    public function getName(): string
    {
        return /** @Desc("Media") */
            $this->translator->trans('tab.name.everyone_media', [], 'dashboard');
    }

    public function getOrder(): int
    {
        return 200;
    }

    public function renderView(array $parameters): string
    {
        /** @todo Handle pagination */
        $page = 1;
        $limit = 10;

        $pager = new Pagerfanta(
            new ContentSearchAdapter(
                $this->mediaSubtreeQueryType->getQuery(),
                $this->searchService
            )
        );
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $this->twig->render('@ibexadesign/ui/dashboard/tab/all_media.html.twig', [
            'data' => $this->pagerContentToDataMapper->map($pager),
        ]);
    }
}

class_alias(EveryoneMediaTab::class, 'EzSystems\EzPlatformAdminUi\Tab\Dashboard\EveryoneMediaTab');
