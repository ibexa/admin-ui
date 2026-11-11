<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\RequestLocaleListener;
use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\EventListener\LocaleAwareListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RequestLocaleListenerTest extends TestCase
{
    private const string ADMIN_SITEACCESS = 'admin_siteaccess';

    private const string NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    private Request & MockObject $request;

    private HttpKernelInterface & MockObject $httpKernel;

    private TranslatorInterface & MockObject $translator;

    private UserLanguagePreferenceProviderInterface & MockObject $userLanguagePreferenceProvider;

    private ConfigResolverInterface & MockObject $configResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createMock(Translator::class);

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getSession', 'hasSession', 'setLocale'])
            ->getMock();

        $this->request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);

        $this->userLanguagePreferenceProvider = $this
            ->getMockBuilder(UserLanguagePreferenceProviderInterface::class)
            ->getMock();

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->configResolver
            ->method('getParameter')
            ->willReturn([]);
    }

    public function testLocaleIsNotSetOnNonAdminSiteaccess(): void
    {
        $translator = $this->translatorWithSetLocaleExpectsNever();

        $request = $this->requestWithSetLocaleExpectsNever();

        $request->attributes->set('siteaccess', new SiteAccess(self::NON_ADMIN_SITEACCESS));

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    public function testLocaleIsNotSetOnSubRequest(): void
    {
        $translator = $this->translatorWithSetLocaleExpectsNever();

        $request = $this->requestWithSetLocaleExpectsNever();

        $request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            [],
            [],
            $translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    public function testLocaleIsSet(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_US');

        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_US');

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userLanguagePreferenceProvider
            ->method('getPreferredLocales')
            ->willReturn(['en_US']);

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            ['en_GB', 'en_US'],
            $this->translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    public function testLocaleIsSetWithoutAvailableTranslation(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_US');

        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_US');

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userLanguagePreferenceProvider
            ->method('getPreferredLocales')
            ->willReturn(['en_US']);

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $this->translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    public function testInvalidPreferredLocaleFallsBackToAvailableTranslation(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_GB');

        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('en_GB');

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // `Accept-Language: *` (RFC 7231 wildcard) surfaces here as the `*` locale, which
        // is not a valid locale; it must not reach the translator.
        $this->userLanguagePreferenceProvider
            ->expects(self::once())
            ->method('getPreferredLocales')
            ->willReturn(['*']);

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            ['en_GB', 'en_US'],
            $this->translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    /**
     * Since symfony/http-kernel 7.4.17, LocaleAwareListener restores the locale every
     * locale-aware service had before a sub-request. The locale set by this listener
     * must therefore be known to all of them, LocaleSwitcher included, or it is lost
     * after the first sub-request.
     */
    public function testLocaleIsKeptAfterSubRequest(): void
    {
        $translator = new Translator('en_GB');
        $localeSwitcher = new LocaleSwitcher('en_GB', [$translator]);
        $requestStack = new RequestStack();
        $localeAwareListener = new LocaleAwareListener([$translator, $localeSwitcher], $requestStack);

        $this->userLanguagePreferenceProvider
            ->method('getPreferredLocales')
            ->willReturn(['fr_FR']);

        $listener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            ['en_GB', 'fr_FR'],
            $translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver,
            $localeSwitcher
        );

        $mainRequest = $this->createAdminRequest('en_GB');
        $requestStack->push($mainRequest);
        $mainRequestEvent = new RequestEvent($this->httpKernel, $mainRequest, HttpKernelInterface::MAIN_REQUEST);
        $localeAwareListener->onKernelRequest($mainRequestEvent);
        $listener->onKernelRequest($mainRequestEvent);

        $subRequest = $this->createAdminRequest('fr_FR');
        $requestStack->push($subRequest);
        $localeAwareListener->onKernelRequest(
            new RequestEvent($this->httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $localeAwareListener->onKernelFinishRequest(
            new FinishRequestEvent($this->httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $requestStack->pop();

        self::assertSame('fr_FR', $mainRequest->getLocale());
        self::assertSame('fr_FR', $translator->getLocale());
        self::assertSame('fr_FR', $localeSwitcher->getLocale());
    }

    public function testSubscribedEvents(): void
    {
        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $this->translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        self::assertSame([KernelEvents::REQUEST => ['onKernelRequest', 6]], $requestLocaleListener::getSubscribedEvents());
    }

    public function testNonSiteaccessInRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Must be an instance of %s', SiteAccess::class));

        $this->request->attributes->set('siteaccess', new Attribute());

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $this->translator,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    /**
     * @return MockObject|TranslatorInterface
     *
     * @throws \ReflectionException
     */
    private function translatorWithSetLocaleExpectsNever(): MockObject
    {
        $translator = $this->createMock(Translator::class);
        $translator
            ->expects(self::never())
            ->method('setLocale');

        return $translator;
    }

    /**
     * @return MockObject|Request
     */
    private function requestWithSetLocaleExpectsNever(): MockObject
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getSession', 'hasSession', 'setLocale'])
            ->getMock();
        $request
            ->expects(self::never())
            ->method('setLocale');

        return $request;
    }

    private function createAdminRequest(string $locale): Request
    {
        $request = new Request();
        $request->setLocale($locale);
        $request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        return $request;
    }
}
