<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\AdminUi\UserSetting\InContextTranslation;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class InContextTranslationListener implements EventSubscriberInterface
{
    private const ACHOLI_LANG = 'ach-UG';

    private UserSettingService $userSettingService;

    /** @var string[] */
    private array $siteAccessGroups;

    private TranslatorInterface $translator;

    public function __construct(
        array $siteAccessGroups,
        UserSettingService $userSettingService,
        TranslatorInterface $translator
    ) {
        $this->siteAccessGroups = $siteAccessGroups;
        $this->userSettingService = $userSettingService;
        $this->translator = $translator;
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

        $request->setLocale(self::ACHOLI_LANG);
        $request->attributes->set('_locale', self::ACHOLI_LANG);
        $this->translator->setLocale(self::ACHOLI_LANG);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}
