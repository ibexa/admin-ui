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
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\LocaleFallbackProvider;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class RequestLocaleListener implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $siteAccessGroups
     * @param string[] $availableTranslations
     */
    public function __construct(
        private array $siteAccessGroups,
        private array $availableTranslations,
        private LocaleSwitcher $localeSwitcher,
        private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private ConfigResolverInterface $configResolver
    ) {}

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

        // Defence-in-depth: the fallback above is the browser's top preference, which is
        // not guaranteed to be a valid locale (e.g. the RFC 7231 `Accept-Language: *`
        // wildcard). Passing such a value to the translator throws an "Invalid locale"
        // error, so fall back to an available translation instead.
        if (!is_string($locale) || !$this->isValidLocale($locale)) {
            $availableTranslations = $this->availableTranslations;
            $locale = reset($availableTranslations);
        }

        if (false === $locale) {
            return;
        }

        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);
        // RequestLocaleListener runs after LocaleAwareListener (it needs the authenticated user), so the locale
        // has to be propagated to all locale-aware services (translator included) through the LocaleSwitcher.
        // Setting it on the translator only would be reverted by LocaleAwareListener after the first sub-request.
        $this->localeSwitcher->setLocale($locale);
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

    private function isValidLocale(string $locale): bool
    {
        if ('' === $locale) {
            return false;
        }

        try {
            LocaleFallbackProvider::validateLocale($locale);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }
}
