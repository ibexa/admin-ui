<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Form\Data\User\FocusModeChangeData;
use Ibexa\AdminUi\Form\Type\User\FocusModeChangeType;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Event\FocusModeChangedEvent;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FocusModeController extends Controller
{
    private const RETURN_URL_PARAM = 'returnUrl';

    private EventDispatcherInterface $eventDispatcher;

    private UserSettingService $userSettingService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserSettingService $userSettingService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userSettingService = $userSettingService;
    }

    public function changeAction(Request $request, ?string $returnUrl): Response
    {
        $data = new FocusModeChangeData();
        $data->setEnabled($this->userSettingService->getUserSetting(FocusMode::IDENTIFIER)->value === FocusMode::FOCUS_MODE_ON);

        $form = $this->createForm(
            FocusModeChangeType::class,
            $data,
            [
                'action' => $this->generateUrl(
                    'ibexa.focus_mode.change',
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
                FocusMode::IDENTIFIER,
                $data->isEnabled() ? FocusMode::FOCUS_MODE_ON : FocusMode::FOCUS_MODE_OFF
            );

            $this->eventDispatcher->dispatch(new FocusModeChangedEvent($data->isEnabled()));

            return $this->createRedirectToReturnUrl($request);
        }

        return $this->render(
            '@ibexadesign/ui/focus_mode_form.html.twig',
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
