<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\AdminUi\UserSetting\InContextTranslation;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class InContextTranslationListener implements EventSubscriberInterface
{
    private UserSettingService $userSettingService;

    /** @var string[] */
    private array $siteAccessGroups;

    public function __construct(
        array $siteAccessGroups,
        UserSettingService $userSettingService
    ) {
        $this->siteAccessGroups = $siteAccessGroups;
        $this->userSettingService = $userSettingService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setInContextTranslation', 5],
            ],
        ];
    }

    public function setInContextTranslation(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || !$this->isAdminSiteAccess($request)) {
            return;
        }

        $inContextSetting = $this->userSettingService->getUserSetting('in_context_translation')->value;

        if ($inContextSetting !== InContextTranslation::ENABLED_OPTION) {
            return;
        }

        $request->setLocale('ach_UG');
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}
