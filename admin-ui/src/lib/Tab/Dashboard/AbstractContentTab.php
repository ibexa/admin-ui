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
use Ibexa\Core\QueryType\QueryType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractContentTab extends AbstractTab implements OrderedTabInterface
{
    protected PagerLocationToDataMapper $pagerLocationToDataMapper;

    protected SearchService $searchService;

    /** @var \Ibexa\AdminUi\QueryType\ContentLocationSubtreeQueryType */
    protected QueryType $contentLocationSubtreeQueryType;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PagerLocationToDataMapper $pagerLocationToDataMapper,
        SearchService $searchService,
        QueryType $contentLocationSubtreeQueryType
    ) {
        parent::__construct($twig, $translator);

        $this->pagerLocationToDataMapper = $pagerLocationToDataMapper;
        $this->searchService = $searchService;
        $this->contentLocationSubtreeQueryType = $contentLocationSubtreeQueryType;
    }
}
