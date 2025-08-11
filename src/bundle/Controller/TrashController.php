<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Search\TrashSearchData;
use Ibexa\AdminUi\Form\Data\Trash\TrashEmptyData;
use Ibexa\AdminUi\Form\Data\Trash\TrashItemDeleteData;
use Ibexa\AdminUi\Form\Data\Trash\TrashItemRestoreData;
use Ibexa\AdminUi\Form\Data\TrashItemData;
use Ibexa\AdminUi\Form\Factory\TrashFormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Search\TrashSearchType;
use Ibexa\AdminUi\Pagination\Pagerfanta\TrashItemAdapter;
use Ibexa\AdminUi\QueryType\TrashSearchQueryType;
use Ibexa\AdminUi\Specification\UserExists;
use Ibexa\AdminUi\UI\Service\PathService as UiPathService;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrashController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly TrashService $trashService,
        private readonly ContentTypeService $contentTypeService,
        private readonly UiPathService $uiPathService,
        private readonly TrashFormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private readonly ConfigResolverInterface $configResolver,
        private readonly TrashSearchQueryType $trashSearchQueryType,
        private readonly UserService $userService
    ) {
    }

    public function performAccessCheck(): void
    {
        parent::performAccessCheck();

        $this->denyAccessUnlessGranted(new Attribute('content', 'restore'));
    }

    /**
     * @throws \LogicException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Pagerfanta\Exception\OutOfRangeCurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1CurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1MaxPerPageException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function listAction(Request $request): Response
    {
        $searchFormName = StringUtil::fqcnToBlockPrefix(TrashSearchType::class);
        $searchForm = $this->formFactory->searchTrash(new TrashSearchData());

        $searchForm->handleRequest($request);

        $requestedPage = $request->query->all($searchFormName)['page'] ?? null;
        $page = empty($requestedPage) ? 1 : (int)$requestedPage;
        $trashItemsList = [];

        $pagerfanta = new Pagerfanta(
            new TrashItemAdapter(
                $this->trashSearchQueryType->getQuery([
                    'search_data' => $searchForm->getData(),
                ]),
                $this->trashService
            )
        );

        $pagerfanta->setMaxPerPage(
            $this->configResolver->getParameter('pagination.trash_limit')
        );
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem $item */
        foreach ($pagerfanta->getCurrentPageResults() as $item) {
            $contentType = $this->contentTypeService->loadContentType(
                $item->getContentInfo()->contentTypeId,
                $this->userLanguagePreferenceProvider->getPreferredLanguages()
            );
            $ancestors = $this->uiPathService->loadPathLocations($item);
            $creator = $this->getCreatorFromTrashItem($item);

            $trashItemsList[] = new TrashItemData($item, $contentType, $ancestors, $creator);
        }

        $trashItemRestoreForm = $this->formFactory->restoreTrashItem(
            new TrashItemRestoreData(iterator_to_array($pagerfanta->getCurrentPageResults()), null)
        );

        $trashItemDeleteForm = $this->formFactory->deleteTrashItem(
            new TrashItemDeleteData(iterator_to_array($pagerfanta->getCurrentPageResults()))
        );

        $trashEmptyForm = $this->formFactory->emptyTrash(
            new TrashEmptyData(true)
        );

        return $this->render('@ibexadesign/trash/list.html.twig', [
            'can_delete' => $this->isGranted(new Attribute('content', 'remove')),
            'can_restore' => $this->isGranted(new Attribute('content', 'restore')),
            'can_cleantrash' => $this->isGranted(new Attribute('content', 'cleantrash')),
            'can_view_section' => $this->isGranted(new Attribute('section', 'view')),
            'trash_items' => $trashItemsList,
            'pager' => $pagerfanta,
            'form_trash_item_restore' => $trashItemRestoreForm,
            'form_trash_item_delete' => $trashItemDeleteForm,
            'form_trash_empty' => $trashEmptyForm,
            'form_search' => $searchForm,
            'user_content_type_identifier' => $this->configResolver->getParameter('user_content_type_identifier'),
        ]);
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function emptyAction(Request $request): Response
    {
        if (!$this->isGranted(new Attribute('content', 'cleantrash'))) {
            return $this->redirectToRoute('ibexa.trash.list');
        }

        $form = $this->formFactory->emptyTrash(
            new TrashEmptyData(true)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (): RedirectResponse {
                $this->trashService->emptyTrash();

                $this->notificationHandler->success(
                    /** @Desc("Trash emptied.") */
                    'trash.empty.success',
                    [],
                    'ibexa_trash'
                );

                return new RedirectResponse($this->generateUrl('ibexa.trash.list'));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.trash.list');
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(Request $request): Response
    {
        if (!$this->isGranted(new Attribute('content', 'restore'))) {
            return $this->redirectToTrashList($request);
        }

        $form = $this->formFactory->restoreTrashItem();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (TrashItemRestoreData $data) use ($request): RedirectResponse {
                    $newParentLocation = $data->getLocation();

                    foreach ($data->getTrashItems() as $trashItem) {
                        $this->trashService->recover($trashItem, $newParentLocation);
                    }

                    if (null === $newParentLocation) {
                        $this->notificationHandler->success(
                            /** @Desc("Restored content to its original Location.") */
                            'trash.restore_original_location.success',
                            [],
                            'ibexa_trash'
                        );
                    } else {
                        $this->notificationHandler->success(
                            /** @Desc("Restored content under Location '%location%'.") */
                            'trash.restore_new_location.success',
                            ['%location%' => $newParentLocation->getContentInfo()->getName()],
                            'ibexa_trash'
                        );
                    }

                    return $this->redirectToTrashList($request);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToTrashList($request);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(Request $request): Response
    {
        if (!$this->isGranted(new Attribute('content', 'remove'))) {
            return $this->redirectToTrashList($request);
        }

        $form = $this->formFactory->deleteTrashItem();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (TrashItemDeleteData $data) use ($request): RedirectResponse {
                    foreach ($data->getTrashItems() as $trashItem) {
                        $this->trashService->deleteTrashItem($trashItem);
                    }

                    $this->notificationHandler->success(
                        /** @Desc("Deleted selected item(s) from Trash.") */
                        'trash.deleted.success',
                        [],
                        'ibexa_trash'
                    );

                    return $this->redirectToTrashList($request);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToTrashList($request);
    }

    private function redirectToTrashList(Request $request): RedirectResponse
    {
        $trashSearchParams = $request->get('trash_search');
        $params = $trashSearchParams ? ['trash_search' => $trashSearchParams] : [];

        return $this->redirectToRoute('ibexa.trash.list', $params);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getCreatorFromTrashItem(TrashItem $trashItem): ?User
    {
        $ownerId = $trashItem->getContentInfo()->getOwner()->getUserId();

        if (false === (new UserExists($this->userService))->isSatisfiedBy($ownerId)) {
            return null;
        }

        return $this->userService->loadUser(
            $trashItem->getContentInfo()->getOwner()->getUserId()
        );
    }
}
