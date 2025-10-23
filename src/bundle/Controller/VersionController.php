<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Version\VersionRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Tab\LocationView\VersionsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Helper\TranslationHelper;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

final class VersionController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly ContentService $contentService,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly TranslationHelper $translationHelper
    ) {}

    /**
     * @throws InvalidOptionsException
     * @throws UnauthorizedException
     * @throws NotFoundException
     * @throws BadStateException
     * @throws InvalidArgumentException
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

        /** @var ContentInfo $contentInfo */
        $contentInfo = $form->getData()->getContentInfo();
        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (VersionRemoveData $data): RedirectResponse {
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
                    'ibexa_admin_ui'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->getId(),
                    'locationId' => $contentInfo->getMainLocationId(),
                    '_fragment' => VersionsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->getId(),
            'locationId' => $contentInfo->getMainLocationId(),
            '_fragment' => VersionsTab::URI_FRAGMENT,
        ]);
    }
}
