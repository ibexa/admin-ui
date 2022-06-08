<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Form\Type\User\UserInvitationType;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\User\Invitation\Exception\InvitationAlreadyExistsException;
use Ibexa\Contracts\User\Invitation\Exception\UserAlreadyExistsException;
use Ibexa\Contracts\User\Invitation\InvitationCreateStruct;
use Ibexa\Contracts\User\Invitation\InvitationSender;
use Ibexa\Contracts\User\Invitation\InvitationService;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class InvitationController
{
    private FormFactoryInterface $formFactory;

    private UserService $userService;

    private InvitationService $invitationService;

    private InvitationSender $sender;

    private Environment $twig;

    private TranslatableNotificationHandlerInterface $notificationHandler;

    private SiteAccessServiceInterface $siteAccessService;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserService $userService,
        InvitationService $invitationService,
        InvitationSender $sender,
        Environment $twig,
        TranslatableNotificationHandlerInterface $notificationHandler,
        SiteAccessServiceInterface $siteAccessService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->formFactory = $formFactory;
        $this->invitationService = $invitationService;
        $this->sender = $sender;
        $this->twig = $twig;
        $this->userService = $userService;
        $this->notificationHandler = $notificationHandler;
        $this->siteAccessService = $siteAccessService;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendInvitationsAction(int $userGroupId, Request $request): Response
    {
        $group = $this->userService->loadUserGroup($userGroupId);

        $form = $this->formFactory->create(
            UserInvitationType::class
        );

        $siteAccess = $this->siteAccessService->getCurrent();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $atLeastOneWasSent = false;
            foreach ($data['emails'] as $email) {
                $struct = new InvitationCreateStruct(
                    $email,
                    $siteAccess->name,
                    $group
                );

                try {
                    $invitation = $this->invitationService->createInvitation($struct);
                    $this->sender->sendInvitation($invitation);
                    $atLeastOneWasSent = true;
                } catch (InvitationAlreadyExistsException $exception) {
                    /** @todo refresh invitation? */
                    $this->notificationHandler->info(
                    /** @Desc("Invitations for %email% already exists") */
                        'ibexa.user.invitations.invitation_exist',
                        [
                            'email' => $struct->getEmail(),
                        ],
                        'user_invitation'
                    );
                } catch (UserAlreadyExistsException $exception) {
                    $this->notificationHandler->info(
                    /** @Desc("User with %email% already exists") */
                        'ibexa.user.invitations.user_exist',
                        [
                            'email' => $struct->getEmail(),
                        ],
                        'user_invitation'
                    );
                }
            }

            if ($atLeastOneWasSent) {
                $this->notificationHandler->success(
                /** @Desc("Invitations sent") */
                    'ibexa.user.invitations.success',
                    [],
                    'user_invitation'
                );

                return new RedirectResponse($this->urlGenerator->generate('ibexa.content.view', [
                    'contentId' => $group->id,
                    'locationId' => $group->contentInfo->mainLocationId,
                ]));
            }
        }

        return new Response($this->twig->render(
            '@ibexadesign/user/invitation/invite_emails.html.twig',
            [
                'form' => $form->createView(),
            ]
        ));
    }
}
