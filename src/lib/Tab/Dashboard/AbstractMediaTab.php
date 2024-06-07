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

abstract class AbstractMediaTab extends AbstractTab implements OrderedTabInterface
{
    /** @var \Ibexa\AdminUi\Tab\Dashboard\PagerLocationToDataMapper */
    protected $pagerLocationToDataMapper;

    /** @var \Ibexa\Contracts\Core\Repository\SearchService */
    protected $searchService;

    /** @var \Ibexa\AdminUi\QueryType\MediaLocationSubtreeQueryType */
    protected $mediaLocationSubtreeQueryType;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PagerLocationToDataMapper $pagerLocationToDataMapper,
        SearchService $searchService,
        QueryType $mediaLocationSubtreeQueryType
    ) {
        parent::__construct($twig, $translator);

        $this->pagerLocationToDataMapper = $pagerLocationToDataMapper;
        $this->searchService = $searchService;
        $this->mediaLocationSubtreeQueryType = $mediaLocationSubtreeQueryType;
    }
}
