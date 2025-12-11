<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Form\Data\User\UserDeleteData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\UserService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserDeleteController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly UserService $userService,
        private readonly LocationService $locationService
    ) {
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function userDeleteAction(Request $request): Response
    {
        $form = $this->formFactory->deleteUser();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (UserDeleteData $data): RedirectResponse {
                $contentInfo = $data->getContentInfo();
                if ($contentInfo === null) {
                    $this->notificationHandler->error(
                        /** @Desc("Deleting user failed.") */
                        'user.delete.error.failed',
                        [],
                        'ibexa_content'
                    );

                    return new RedirectResponse($this->generateUrl('ibexa.dashboard'));
                }

                $mainLocationId = $contentInfo->getMainLocationId();
                if ($mainLocationId === null) {
                    $this->notificationHandler->error(
                        /** @Desc("Deleting user failed. User has no main location.") */
                        'user.delete.error.no_main_location',
                        [],
                        'ibexa_content'
                    );

                    return new RedirectResponse($this->generateUrl('ibexa.dashboard'));
                }

                $location = $this->locationService->loadLocation($mainLocationId);
                $parentLocation = $this->locationService->loadLocation($location->parentLocationId);

                $user = $this->userService->loadUser($contentInfo->getId());

                $this->userService->deleteUser($user);

                $this->notificationHandler->success(
                    /** @Desc("User with login '%login%' deleted.") */
                    'user.delete.success',
                    ['%login%' => $user->login],
                    'ibexa_content'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $parentLocation->getContentId(),
                    'locationId' => $location->parentLocationId,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }
}
