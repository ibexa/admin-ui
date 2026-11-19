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
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\Translation\Translator;

final class RequestLocaleListenerTest extends AbstractLocaleListenerTestCase
{
    private const string NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    private Request $request;

    private HttpKernelInterface & Stub $httpKernel;

    private LocaleSwitcher & MockObject $localeSwitcher;

    private UserLanguagePreferenceProviderInterface & MockObject $userLanguagePreferenceProvider;

    private ConfigResolverInterface & MockObject $configResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);

        $this->request = $this->createRequest(self::ADMIN_SITEACCESS);

        $this->httpKernel = self::createStub(HttpKernelInterface::class);

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
        $localeSwitcher = $this->localeSwitcherWithSetLocaleExpectsNever();

        $request = $this->createRequest(self::NON_ADMIN_SITEACCESS);

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);

        self::assertSame($request->getDefaultLocale(), $request->getLocale());
        self::assertFalse($request->attributes->has('_locale'));
    }

    public function testLocaleIsNotSetOnSubRequest(): void
    {
        $localeSwitcher = $this->localeSwitcherWithSetLocaleExpectsNever();

        $request = $this->createRequest(self::ADMIN_SITEACCESS);

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            [],
            [],
            $localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);

        self::assertSame($request->getDefaultLocale(), $request->getLocale());
        self::assertFalse($request->attributes->has('_locale'));
    }

    public function testLocaleIsSet(): void
    {
        $this->localeSwitcher
            ->expects($this->once())
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
            $this->localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);

        self::assertSame('en_US', $this->request->getLocale());
        self::assertSame('en_US', $this->request->attributes->get('_locale'));
    }

    public function testLocaleIsSetWithoutAvailableTranslation(): void
    {
        $this->localeSwitcher
            ->expects($this->once())
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
            $this->localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);

        self::assertSame('en_US', $this->request->getLocale());
        self::assertSame('en_US', $this->request->attributes->get('_locale'));
    }

    public function testInvalidPreferredLocaleFallsBackToAvailableTranslation(): void
    {
        $this->localeSwitcher
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn(['*']);

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            ['en_GB', 'en_US'],
            $this->localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);

        self::assertSame('en_GB', $this->request->getLocale());
        self::assertSame('en_GB', $this->request->attributes->get('_locale'));
    }

    public function testLocaleIsKeptAfterSubRequest(): void
    {
        $this->userLanguagePreferenceProvider
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn(['fr_FR']);

        $this->assertLocaleIsKeptAfterSubRequest(
            fn (Translator $translator, LocaleSwitcher $localeSwitcher): callable => [
                new RequestLocaleListener(
                    ['admin_group' => [self::ADMIN_SITEACCESS]],
                    ['en_GB', 'fr_FR'],
                    $localeSwitcher,
                    $this->userLanguagePreferenceProvider,
                    $this->configResolver
                ),
                'onKernelRequest',
            ],
            'fr_FR'
        );
    }

    public function testSubscribedEvents(): void
    {
        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $this->localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        self::assertSame([KernelEvents::REQUEST => ['onKernelRequest', 6]], $requestLocaleListener::getSubscribedEvents());
    }

    public function testNonSiteaccessInRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Must be an instance of %s', SiteAccess::class));

        $request = new Request(attributes: [
            'siteaccess' => new Attribute(),
        ]);

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $requestLocaleListener = new RequestLocaleListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            [],
            $this->localeSwitcher,
            $this->userLanguagePreferenceProvider,
            $this->configResolver
        );

        $requestLocaleListener->onKernelRequest($event);
    }

    private function localeSwitcherWithSetLocaleExpectsNever(): LocaleSwitcher & MockObject
    {
        $localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $localeSwitcher
            ->expects($this->never())
            ->method('setLocale');

        return $localeSwitcher;
    }

    private function createRequest(string $siteAccessName): Request
    {
        return new Request(attributes: [
            'siteaccess' => new SiteAccess($siteAccessName),
        ]);
    }
}
