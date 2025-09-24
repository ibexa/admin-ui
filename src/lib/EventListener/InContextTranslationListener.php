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

final readonly class InContextTranslationListener implements EventSubscriberInterface
{
    private const string ACHOLI_LANG = 'ach-UG';

    /**
     * @param array<string, string[]> $siteAccessGroups
     */
    public function __construct(
        private array $siteAccessGroups,
        private UserSettingService $userSettingService,
        private TranslatorInterface $translator
    ) {
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

        $inContextSetting = $this->userSettingService->getUserSetting('in_context_translation')->getValue();

        if ($inContextSetting !== InContextTranslation::ENABLED_OPTION) {
            return;
        }

        $request->setLocale(self::ACHOLI_LANG);
        $request->attributes->set('_locale', self::ACHOLI_LANG);
        $this->translator->setLocale(self::ACHOLI_LANG);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
