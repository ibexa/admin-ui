<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\AdminUi\Form\Data\Version\VersionRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Tab\LocationView\VersionsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionController extends Controller
{
    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

    /** @var \Ibexa\Core\Helper\TranslationHelper */
    private $translationHelper;

    /**
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     * @param \Ibexa\Core\Helper\TranslationHelper $translationHelper
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
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function removeAction(Request $request): Response
    {
        $isDraftForm = null !== $request->get(
            sprintf('version-remove-%s', VersionsTab::FORM_REMOVE_DRAFT)
        );

        $formName = $isDraftForm
            ? sprintf('version-remove-%s', VersionsTab::FORM_REMOVE_DRAFT)
            : sprintf('version-remove-%s', VersionsTab::FORM_REMOVE_ARCHIVED);

        $form = $this->formFactory->removeVersion(
            new VersionRemoveData(),
            $formName
        );
        $form->handleRequest($request);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
        $contentInfo = $form->getData()->getContentInfo();
        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (VersionRemoveData $data) {
                $contentInfo = $data->getContentInfo();

                foreach ($data->getVersions() as $versionNo => $selected) {
                    $versionInfo = $this->contentService->loadVersionInfo($contentInfo, $versionNo);
                    $this->contentService->deleteVersion($versionInfo);
                }

                $this->notificationHandler->success(
                    /** @Desc("Removed version(s) from '%name%'.") */
                    'version.delete.success',
                    [
                        '%name%' => $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo),
                    ],
                    'version'
                );

                return new RedirectResponse($this->generateUrl('_ez_content_view', [
                    'contentId' => $contentInfo->id,
                    'locationId' => $contentInfo->mainLocationId,
                    '_fragment' => VersionsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirect($this->generateUrl('_ez_content_view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => VersionsTab::URI_FRAGMENT,
        ]));
    }
}

class_alias(VersionController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\VersionController');
