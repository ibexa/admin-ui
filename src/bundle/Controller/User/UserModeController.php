<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Form\Data\User\UserModeChangeData;
use Ibexa\AdminUi\Form\Type\User\UserModeChangeType;
use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class UserModeController extends Controller
{
    private UserSettingService $userSettingService;

    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    public function changeAction(RequestStack $requestStack, Request $request): Response
    {
        $data = new UserModeChangeData();
        $data->setMode($this->userSettingService->getUserSetting(UserMode::IDENTIFIER)->value === UserMode::EXPERT);

        $form = $this->createForm(
            UserModeChangeType::class,
            $data,
            [
                'action' => $this->generateUrl('ibexa.user_mode.change', [
                    'referrer' => $requestStack->getMainRequest() !== null ? $requestStack->getMainRequest()->getRequestUri() : null,
                ]),
                'method' => Request::METHOD_POST,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userSettingService->setUserSetting(
                UserMode::IDENTIFIER,
                $data->getMode() ? UserMode::EXPERT : UserMode::SMART
            );

            return $this->redirect($request->query->get('referrer'));
        }

        return $this->render(
            '@ibexadesign/ui/user_mode_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
