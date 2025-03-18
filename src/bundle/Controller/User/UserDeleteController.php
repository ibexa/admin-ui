<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

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

class UserDeleteController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private UserService $userService;

    private LocationService $locationService;

    /**
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        UserService $userService,
        LocationService $locationService
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->userService = $userService;
        $this->locationService = $locationService;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function userDeleteAction(Request $request): Response
    {
        $form = $this->formFactory->deleteUser();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (UserDeleteData $data): RedirectResponse {
                $contentInfo = $data->getContentInfo();

                $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
                $parentLocation = $this->locationService->loadLocation($location->parentLocationId);

                $user = $this->userService->loadUser($contentInfo->id);

                $this->userService->deleteUser($user);

                $this->notificationHandler->success(
                    /** @Desc("User with login '%login%' deleted.") */
                    'user.delete.success',
                    ['%login%' => $user->login],
                    'ibexa_content'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $parentLocation->contentId,
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
