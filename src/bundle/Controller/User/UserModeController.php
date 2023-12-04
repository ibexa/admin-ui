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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserModeController extends Controller
{
    private const RETURN_URL_PARAM = 'returnUrl';

    private UserSettingService $userSettingService;

    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    public function changeAction(Request $request, ?string $returnUrl): Response
    {
        $data = new UserModeChangeData();
        $data->setMode($this->userSettingService->getUserSetting(UserMode::IDENTIFIER)->value === UserMode::EXPERT);

        $form = $this->createForm(
            UserModeChangeType::class,
            $data,
            [
                'action' => $this->generateUrl(
                    'ibexa.user_mode.change',
                    [
                        self::RETURN_URL_PARAM => $returnUrl,
                    ]
                ),
                'method' => Request::METHOD_POST,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userSettingService->setUserSetting(
                UserMode::IDENTIFIER,
                $data->getMode() ? UserMode::EXPERT : UserMode::SMART
            );

            return $this->createRedirectToReturnUrl($request);
        }

        return $this->render(
            '@ibexadesign/ui/user_mode_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function createRedirectToReturnUrl(Request $request): RedirectResponse
    {
        $url = $request->query->get(self::RETURN_URL_PARAM);
        if (is_string($url) && $this->isSafeUrl($url, $request->getBaseUrl())) {
            return new RedirectResponse($url);
        }

        throw $this->createAccessDeniedException('Malformed return URL');
    }

    private function isSafeUrl(string $referer, string $baseUrl): bool
    {
        return str_starts_with($referer, $baseUrl);
    }
}
