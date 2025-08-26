<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RequestLocaleListener implements EventSubscriberInterface
{
    /**
     * @param string[][] $siteAccessGroups
     * @param string[] $availableTranslations
     */
    public function __construct(
        private array $siteAccessGroups,
        private array $availableTranslations,
        private TranslatorInterface $translator,
        private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private ConfigResolverInterface $configResolver
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 6],
        ];
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || !$this->isAdminSiteAccess($request)) {
            return;
        }

        $additionalTranslations = $this->configResolver->getParameter(
            'user_preferences.additional_translations'
        );

        $preferableLocales = $this->userLanguagePreferenceProvider->getPreferredLocales($request);
        $locale = null;

        foreach ($preferableLocales as $preferableLocale) {
            if (in_array($preferableLocale, $this->availableTranslations, true)
                || in_array($preferableLocale, $additionalTranslations, true)
            ) {
                $locale = $preferableLocale;
                break;
            }
        }
        $locale = $locale ?? reset($preferableLocales);
        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);
        // Set of the current locale on the translator service is needed because RequestLocaleListener has lower
        // priority than LocaleListener and messages are not translated on login, forgot and reset password pages.
        $this->translator->setLocale($locale);
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
