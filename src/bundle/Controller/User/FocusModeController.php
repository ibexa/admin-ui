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
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

final class FocusModeController extends Controller
{
    private const RETURN_PATH_PARAM = 'returnPath';

    private EventDispatcherInterface $eventDispatcher;

    private UserSettingService $userSettingService;

    private UrlMatcherInterface $urlMatcher;

    /** @var iterable<\Ibexa\Contracts\AdminUi\FocusMode\RedirectStrategyInterface> */
    private iterable $redirectStrategies;

    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\FocusMode\RedirectStrategyInterface> $redirectStrategies
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserSettingService $userSettingService,
        UrlMatcherInterface $urlMatcher,
        iterable $redirectStrategies
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userSettingService = $userSettingService;
        $this->urlMatcher = $urlMatcher;
        $this->redirectStrategies = $redirectStrategies;
    }

    public function changeAction(Request $request, ?string $returnPath): Response
    {
        $data = new FocusModeChangeData();
        $data->setEnabled(
            $this->userSettingService->getUserSetting(FocusMode::IDENTIFIER)->value === FocusMode::FOCUS_MODE_ON
        );

        $form = $this->createForm(
            FocusModeChangeType::class,
            $data,
            [
                'action' => $this->generateUrl(
                    'ibexa.focus_mode.change',
                    [
                        self::RETURN_PATH_PARAM => $returnPath,
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

            return $this->createRedirectToReturnPath($request);
        }

        return $this->render(
            '@ibexadesign/ui/focus_mode_form.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    private function createRedirectToReturnPath(Request $request): RedirectResponse
    {
        $path = $this->resolveReturnPath(
            $request->query->get(self::RETURN_PATH_PARAM) ?? ''
        );

        if (!$this->isSafeUrl($path, $request->getBaseUrl())) {
            throw $this->createAccessDeniedException('Malformed return path');
        }

        return new RedirectResponse($path);
    }

    private function isSafeUrl(string $referer, string $baseUrl): bool
    {
        return str_starts_with($referer, $baseUrl);
    }

    private function resolveReturnPath(string $path): string
    {
        $rootRouteInfo = $this->urlMatcher->match('/');
        $rootRoute = $this->generateUrl($rootRouteInfo['_route'], $rootRouteInfo);

        $rootPath = rtrim(parse_url($rootRoute, PHP_URL_PATH) ?: '', '/');
        $route = $this->matchRouteByPath($path);

        $fullPath = $rootPath . $path;
        foreach ($this->redirectStrategies as $strategy) {
            if ($strategy->supports($route)) {
                return $strategy->generateRedirectPath($fullPath);
            }
        }

        return $fullPath;
    }

    /**
     * @return array<string, string>
     */
    private function matchRouteByPath(string $path): array
    {
        $originalContext = $this->urlMatcher->getContext();

        $context = clone $originalContext;
        $context->setMethod('GET');

        $this->urlMatcher->setContext($context);
        $routeData = $this->urlMatcher->match($path);

        $this->urlMatcher->setContext($originalContext);

        return $routeData;
    }
}
