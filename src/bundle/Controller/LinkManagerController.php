<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Pagination\Pagerfanta\URLUsagesAdapter;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LinkManagerController extends Controller
{
    public const int DEFAULT_MAX_PER_PAGE = 10;

    public function __construct(
        private readonly URLService $urlService,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly TranslatableNotificationHandlerInterface $notificationHandler
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function editAction(Request $request, int $urlId): Response
    {
        $url = $this->urlService->loadById($urlId);

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createUrlEditForm(new URLUpdateData([
            'id' => $url->getId(),
            'url' => $url->getUrl(),
        ]));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (URLUpdateData $data) use ($url, $form): Response {
                $this->urlService->updateUrl($url, $data);
                $this->notificationHandler->success(
                    /** @Desc("URL updated") */
                    'url.update.success',
                    [],
                    'ibexa_linkmanager'
                );

                return $this->redirectToRoute('ibexa.url_management');
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/link_manager/edit.html.twig', [
            'form' => $form,
            'url' => $url,
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function viewAction(Request $request, int $urlId): Response
    {
        $url = $this->urlService->loadById($urlId);

        $usages = new Pagerfanta(new URLUsagesAdapter($url, $this->urlService));
        $usages->setCurrentPage($request->query->getInt('page', 1));
        $usages->setMaxPerPage($request->query->getInt('limit', self::DEFAULT_MAX_PER_PAGE));

        $editForm = $this->formFactory->contentEdit(new ContentEditData());

        return $this->render('@ibexadesign/link_manager/view.html.twig', [
            'url' => $url,
            'can_edit' => $this->isGranted(new Attribute('url', 'update')),
            'usages' => $usages,
            'form_edit' => $editForm,
        ]);
    }
}
