<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\InContextTranslationListener;
use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\UserSetting\InContextTranslation;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
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

final class InContextTranslationListenerTest extends TestCase
{
    private const ADMIN_SITEACCESS = 'admin_siteaccess';

    private const NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    private Request & MockObject $request;

    private HttpKernelInterface & MockObject $httpKernel;

    private UserSettingService & MockObject $userSettingService;

    private LocaleSwitcher & MockObject $localeSwitcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['setLocale'])
            ->getMock();

        $this->request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);

        $this->userSettingService = $this->createMock(UserSettingService::class);

        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);
    }

    public function testLocaleIsNotSetOnNonAdminSiteaccess(): void
    {
        $request = $this->requestWithSetLocaleExpectsNever();

        $request->attributes->set('siteaccess', new SiteAccess(self::NON_ADMIN_SITEACCESS));

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        $listener->setInContextTranslation($event);
    }

    public function testLocaleIsNotSetOnSubRequest(): void
    {
        $request = $this->requestWithSetLocaleExpectsNever();

        $request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        $listener->setInContextTranslation($event);
    }

    public function testLocaleIsSet(): void
    {
        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('ach-UG');

        $this->localeSwitcher
            ->expects(self::once())
            ->method('setLocale')
            ->with('ach-UG');

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userSettingService
            ->method('getUserSetting')
            ->with('in_context_translation')
            ->willReturn(
                new UserSetting(['value' => InContextTranslation::ENABLED_OPTION])
            );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        $listener->setInContextTranslation($event);
    }

    public function testLocaleIsNotSet(): void
    {
        $this->request
            ->expects(self::never())
            ->method('setLocale');

        $event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userSettingService
            ->method('getUserSetting')
            ->with('in_context_translation')
            ->willReturn(
                new UserSetting(['value' => InContextTranslation::DISABLED_OPTION])
            );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        $listener->setInContextTranslation($event);
    }

    /**
     * Since symfony/http-kernel 7.4.17, LocaleAwareListener restores the locale every
     * locale-aware service had before a sub-request. The in-context translation locale
     * must therefore be known to all of them, LocaleSwitcher included, or it is lost
     * after the first sub-request.
     */
    public function testLocaleIsKeptAfterSubRequest(): void
    {
        $translator = new Translator('en_GB');
        $localeSwitcher = new LocaleSwitcher('en_GB', [$translator]);
        $requestStack = new RequestStack();
        $localeAwareListener = new LocaleAwareListener([$translator, $localeSwitcher], $requestStack);

        $this->userSettingService
            ->method('getUserSetting')
            ->with('in_context_translation')
            ->willReturn(
                new UserSetting(['value' => InContextTranslation::ENABLED_OPTION])
            );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $localeSwitcher
        );

        $mainRequest = $this->createAdminRequest('en_GB');
        $requestStack->push($mainRequest);
        $mainRequestEvent = new RequestEvent($this->httpKernel, $mainRequest, HttpKernelInterface::MAIN_REQUEST);
        $localeAwareListener->onKernelRequest($mainRequestEvent);
        $listener->setInContextTranslation($mainRequestEvent);

        $subRequest = $this->createAdminRequest('ach-UG');
        $requestStack->push($subRequest);
        $localeAwareListener->onKernelRequest(
            new RequestEvent($this->httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $localeAwareListener->onKernelFinishRequest(
            new FinishRequestEvent($this->httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $requestStack->pop();

        self::assertSame('ach-UG', $mainRequest->getLocale());
        self::assertSame('ach-UG', $translator->getLocale());
        self::assertSame('ach-UG', $localeSwitcher->getLocale());
    }

    public function testSubscribedEvents(): void
    {
        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        self::assertSame(
            [KernelEvents::REQUEST => [['setInContextTranslation', 5]]],
            $listener::getSubscribedEvents()
        );
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

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->localeSwitcher
        );

        $listener->setInContextTranslation($event);
    }

    /**
     * @return MockObject|Request
     */
    private function requestWithSetLocaleExpectsNever(): MockObject
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['setLocale'])
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
