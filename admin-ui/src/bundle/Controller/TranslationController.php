<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationDeleteData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Tab\LocationView\TranslationsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Core\Helper\TranslationHelper;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private ContentService $contentService;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private TranslationHelper $translationHelper;

    /**
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     */
    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        ContentService $contentService,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        TranslationHelper $translationHelper
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->contentService = $contentService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request): Response
    {
        $formName = $request->query->get('formName');
        $form = $this->formFactory->addTranslation(null, $formName);
        $form->handleRequest($request);

        /** @var \Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData $data */
        $data = $form->getData();
        $location = $data->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (TranslationAddData $data): RedirectResponse {
                $location = $data->getLocation();
                $contentInfo = $location->getContentInfo();
                $language = $data->getLanguage();
                $baseLanguage = $data->getBaseLanguage();

                return new RedirectResponse($this->generateUrl('ibexa.content.translate_with_location.proxy', [
                    'contentId' => $contentInfo->id,
                    'fromLanguageCode' => null !== $baseLanguage ? $baseLanguage->languageCode : null,
                    'toLanguageCode' => $language->languageCode,
                    'locationId' => $location->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        $redirectionUrl = null !== $location
            ? $this->generateUrl('ibexa.content.view', [
                'contentId' => $location->contentId,
                'locationId' => $location->id,
            ])
            : $this->generateUrl('ibexa.dashboard');

        return $this->redirect($redirectionUrl);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request): Response
    {
        $form = $this->formFactory->deleteTranslation();
        $form->handleRequest($request);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
        $contentInfo = $form->getData()->getContentInfo();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (TranslationDeleteData $data): RedirectResponse {
                $contentInfo = $data->getContentInfo();

                foreach ($data->getLanguageCodes() as $languageCode => $selected) {
                    $this->contentService->deleteTranslation($contentInfo, $languageCode);

                    $this->notificationHandler->success(
                        /** @Desc("Removed '%languageCode%' translation from '%name%'.") */
                        'translation.remove.success',
                        [
                            '%languageCode%' => $languageCode,
                            '%name%' => $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo),
                        ],
                        'ibexa_admin_ui'
                    );
                }

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->id,
                    'locationId' => $contentInfo->mainLocationId,
                    '_fragment' => TranslationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => TranslationsTab::URI_FRAGMENT,
        ]);
    }
}
