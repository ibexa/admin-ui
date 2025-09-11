<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\AdminUi\QueryType\MediaLocationSubtreeQueryType;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractMediaTab extends AbstractTab implements OrderedTabInterface
{
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        protected readonly PagerLocationToDataMapper $pagerLocationToDataMapper,
        protected readonly SearchService $searchService,
        protected readonly MediaLocationSubtreeQueryType $mediaLocationSubtreeQueryType
    ) {
        parent::__construct($twig, $translator);
    }
}
