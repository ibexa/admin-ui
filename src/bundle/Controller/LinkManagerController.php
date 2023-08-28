<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\URL\URLEditType;
use Ibexa\AdminUi\Pagination\Pagerfanta\URLUsagesAdapter;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Button;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LinkManagerController extends Controller
{
    public const DEFAULT_MAX_PER_PAGE = 10;

    /** @var \Ibexa\Contracts\Core\Repository\URLService */
    private $urlService;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /**
     * @param \Ibexa\Contracts\Core\Repository\URLService $urlService
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     */
    public function __construct(
        URLService $urlService,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        TranslatableNotificationHandlerInterface $notificationHandler
    ) {
        $this->urlService = $urlService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $urlId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function editAction(Request $request, int $urlId): Response
    {
        $url = $this->urlService->loadById($urlId);

        $form = $this->formFactory->createUrlEditForm(new URLUpdateData([
            'id' => $url->id,
            'url' => $url->url,
        ]));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (URLUpdateData $data) use ($url, $form) {
                $this->urlService->updateUrl($url, $data);
                $this->notificationHandler->success(
                    /** @Desc("URL updated") */
                    'url.update.success',
                    [],
                    'ibexa_linkmanager'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === URLEditType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.link_manager.edit', [
                        'urlId' => $url->id,
                    ]);
                }

                return $this->redirectToRoute('ibexa.url_management');
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/link_manager/edit.html.twig', [
            'form' => $form->createView(),
            'url' => $url,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $urlId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function viewAction(Request $request, int $urlId): Response
    {
        $url = $this->urlService->loadById($urlId);

        $usages = new Pagerfanta(new URLUsagesAdapter($url, $this->urlService));
        $usages->setCurrentPage($request->query->getInt('page', 1));
        $usages->setMaxPerPage($request->query->getInt('limit', self::DEFAULT_MAX_PER_PAGE));

        $editForm = $this->formFactory->contentEdit(
            new ContentEditData()
        );

        return $this->render('@ibexadesign/link_manager/view.html.twig', [
            'url' => $url,
            'can_edit' => $this->isGranted(new Attribute('url', 'update')),
            'usages' => $usages,
            'form_edit' => $editForm->createView(),
        ]);
    }
}

class_alias(LinkManagerController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\LinkManagerController');
