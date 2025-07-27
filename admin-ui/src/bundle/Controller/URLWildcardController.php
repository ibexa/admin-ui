<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardDeleteData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardUpdateType;
use Ibexa\AdminUi\Tab\URLManagement\URLWildcardsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcardUpdateStruct;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Button;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class URLWildcardController extends Controller
{
    private URLWildcardService $urlWildcardService;

    private TranslatableNotificationHandlerInterface $notificationHandler;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    public function __construct(
        URLWildcardService $urlWildcardService,
        TranslatableNotificationHandlerInterface $notificationHandler,
        FormFactory $formFactory,
        SubmitHandler $submitHandler
    ) {
        $this->urlWildcardService = $urlWildcardService;
        $this->notificationHandler = $notificationHandler;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createURLWildcard();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->submitHandler->handle($form, function (URLWildcardData $data) use ($form): Response {
                $urlWildcard = $this->urlWildcardService->create(
                    $data->getSourceURL(),
                    $data->getDestinationUrl(),
                    (bool) $data->getForward()
                );

                $this->notificationHandler->success(
                    /** @Desc("URL Wildcard created.") */
                    'url_wildcard.create.success',
                    [],
                    'ibexa_url_wildcard'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === URLWildcardUpdateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.url_wildcard.update', [
                        'urlWildcardId' => $urlWildcard->id,
                    ]);
                }

                return $this->redirectToRoute('ibexa.url_management', [
                    '_fragment' => URLWildcardsTab::URI_FRAGMENT,
                ]);
            });
        }

        return $this->redirectToRoute('ibexa.url_management', [
            '_fragment' => URLWildcardsTab::URI_FRAGMENT,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard $urlWildcard
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(URLWildcard $urlWildcard, Request $request): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createURLWildcardUpdate(
            new URLWildcardUpdateData($urlWildcard)
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (URLWildcardUpdateData $data) use ($urlWildcard, $form): Response {
                    $urlWildcardUpdateStruct = new URLWildcardUpdateStruct();
                    $urlWildcardUpdateStruct->destinationUrl = $data->getDestinationUrl();
                    $urlWildcardUpdateStruct->sourceUrl = $data->getSourceURL();
                    $urlWildcardUpdateStruct->forward = $data->getForward();

                    $this->urlWildcardService->update(
                        $urlWildcard,
                        $urlWildcardUpdateStruct
                    );

                    $this->notificationHandler->success(
                        /** @Desc("URL Wildcard updated.") */
                        'url_wildcard.update.success',
                        [],
                        'ibexa_url_wildcard'
                    );

                    if ($form->getClickedButton() instanceof Button
                        && $form->getClickedButton()->getName() === URLWildcardUpdateType::BTN_SAVE
                    ) {
                        return $this->redirectToRoute('ibexa.url_wildcard.update', [
                            'urlWildcardId' => $urlWildcard->id,
                        ]);
                    }

                    return $this->redirectToRoute('ibexa.url_management', [
                        '_fragment' => URLWildcardsTab::URI_FRAGMENT,
                    ]);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        $actionUrl = $this->generateUrl(
            'ibexa.url_wildcard.update',
            ['urlWildcardId' => $urlWildcard->id]
        );

        return $this->render('@ibexadesign/url_wildcard/update.html.twig', [
            'form' => $form,
            'actionUrl' => $actionUrl,
            'urlWildcard' => $urlWildcard,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkDeleteAction(Request $request): Response
    {
        $form = $this->formFactory->deleteURLWildcard();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->submitHandler->handle($form, function (URLWildcardDeleteData $data): void {
                foreach ($data->getURLWildcardsChoices() as $urlWildcardId => $value) {
                    $urlWildcard = $this->urlWildcardService->load($urlWildcardId);
                    $this->urlWildcardService->remove($urlWildcard);
                }
            });

            $this->notificationHandler->success(
                /** @Desc("URL Wildcard(s) deleted.") */
                'url_wildcard.delete.success',
                [],
                'ibexa_url_wildcard'
            );
        }

        return $this->redirectToRoute('ibexa.url_management', [
            '_fragment' => URLWildcardsTab::URI_FRAGMENT,
        ]);
    }
}
