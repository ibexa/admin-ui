<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Pagerfanta\Pagerfanta;

class MyMediaTab extends AbstractMediaTab implements OrderedTabInterface
{
    public function getIdentifier(): string
    {
        return 'my-media';
    }

    public function getName(): string
    {
        return /** @Desc("Media") */
            $this->translator->trans('tab.name.my_media', [], 'dashboard');
    }

    public function getOrder(): int
    {
        return 300;
    }

    /**
     * @throws \Twig\Error\Error
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function renderView(array $parameters): string
    {
        /** @todo Handle pagination */
        $page = 1;
        $limit = 10;

        $pager = new Pagerfanta(
            new LocationSearchAdapter(
                $this->mediaLocationSubtreeQueryType->getQuery(['owned' => true]),
                $this->searchService
            )
        );
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $this->twig->render('@ibexadesign/ui/dashboard/tab/my_media.html.twig', [
            'data' => $this->pagerLocationToDataMapper->map($pager, true),
        ]);
    }
}

class_alias(MyMediaTab::class, 'EzSystems\EzPlatformAdminUi\Tab\Dashboard\MyMediaTab');
