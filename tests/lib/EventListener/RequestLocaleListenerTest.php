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
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\Translator;

class RequestLocaleListenerTest extends TestCase
{
    private const ADMIN_SITEACCESS = 'admin_siteaccess';

    private const NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpKernel\HttpKernelInterface */
    private $httpKernel;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Contracts\Translation\TranslatorInterface
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
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
}
