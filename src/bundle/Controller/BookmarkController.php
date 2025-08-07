<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Bookmark\BookmarkRemoveData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Pagination\Pagerfanta\BookmarkAdapter;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BookmarkController extends Controller
{
    public function __construct(
        private readonly BookmarkService $bookmarkService,
        private readonly DatasetFactory $datasetFactory,
        private readonly FormFactory $formFactory,
        private readonly LocationService $locationService,
        private readonly SubmitHandler $submitHandler,
        private readonly ConfigResolverInterface $configResolver
    ) {
    }

    public function listAction(Request $request): Response
    {
        /** @phpstan-var int<0, max> $page */
        $page = $request->query->getInt('page', 1);

        $pagerfanta = new Pagerfanta(
            new BookmarkAdapter($this->bookmarkService, $this->datasetFactory)
        );

        $pagerfanta->setMaxPerPage(
            $this->configResolver->getParameter('pagination.bookmark_limit')
        );
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        $editForm = $this->formFactory->contentEdit(
            new ContentEditData()
        );

        $removeBookmarkForm = $this->formFactory->removeBookmark(
            new BookmarkRemoveData(
                $this->getChoices(iterator_to_array($pagerfanta->getCurrentPageResults()))
            )
        );

        return $this->render(
            '@ibexadesign/account/bookmarks/list.html.twig',
            [
                'pager' => $pagerfanta,
                'form_edit' => $editForm,
                'form_remove' => $removeBookmarkForm,
            ]
        );
    }

    public function removeAction(Request $request): Response
    {
        $form = $this->formFactory->removeBookmark(
            new BookmarkRemoveData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (BookmarkRemoveData $data): RedirectResponse {
                    foreach ($data->getBookmarks() as $locationId => $selected) {
                        $this->bookmarkService->deleteBookmark(
                            $this->locationService->loadLocation($locationId)
                        );
                    }

                    return $this->redirectToRoute('ibexa.bookmark.list');
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.bookmark.list');
    }

    /**
     * @param array<\Ibexa\AdminUi\UI\Value\Location\Bookmark> $bookmarks
     *
     * @return array<int, false>
     */
    private function getChoices(array $bookmarks): array
    {
        $bookmarks = array_column($bookmarks, 'id');

        return array_combine($bookmarks, array_fill_keys($bookmarks, false));
    }
}
