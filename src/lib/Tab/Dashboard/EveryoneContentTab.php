<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;

class EveryoneContentTab extends AbstractContentTab implements OrderedTabInterface
{
    public function getIdentifier(): string
    {
        return 'everyone-content';
    }

    public function getName(): string
    {
        return /** @Desc("Content") */
            $this->translator->trans('tab.name.everyone_content', [], 'ibexa_dashboard');
    }

    public function getOrder(): int
    {
        return 100;
    }

    public function renderView(array $parameters): string
    {
        /** @todo Handle pagination */
        $page = 1;
        $limit = 10;

        $pager = new Pagerfanta(
            new LocationSearchAdapter(
                $this->contentLocationSubtreeQueryType->getQuery(),
                $this->searchService
            )
        );
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $this->twig->render('@ibexadesign/ui/dashboard/tab/all_content.html.twig', [
            'data' => $this->pagerLocationToDataMapper->map($pager, true),
        ]);
    }
}
