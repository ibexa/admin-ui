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
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\Translator;

final class InContextTranslationListenerTest extends TestCase
{
    private const ADMIN_SITEACCESS = 'admin_siteaccess';

    private const NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpKernel\HttpKernelInterface */
    private $httpKernel;

    /** @var \Ibexa\User\UserSetting\UserSettingService|\PHPUnit\Framework\MockObject\MockObject */
    private $userSettingService;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

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

        $this->translator = $this->createMock(Translator::class);
    }

    public function testLocaleIsNotSetOnNonAdminSiteaccess(): void
    {
        $request = $this->requestWithSetLocaleExpectsNever();

        $request->attributes->set('siteaccess', new SiteAccess(self::NON_ADMIN_SITEACCESS));

        $event = new RequestEvent(
            $this->httpKernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->translator
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
            $this->translator
        );

        $listener->setInContextTranslation($event);
    }

    public function testLocaleIsSet(): void
    {
        $this->request
            ->expects($this->once())
            ->method('setLocale')
            ->with('ach_UG');

        $this->translator
            ->expects($this->once())
            ->method('setLocale')
            ->with('ach_UG');

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
            $this->translator
        );

        $listener->setInContextTranslation($event);
    }

    public function testLocaleIsNotSet(): void
    {
        $this->request
            ->expects($this->never())
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
            $this->translator
        );

        $listener->setInContextTranslation($event);
    }

    public function testSubscribedEvents(): void
    {
        $listener = new InContextTranslationListener(
            ['admin_group' => [self::ADMIN_SITEACCESS]],
            $this->userSettingService,
            $this->translator
        );

        $this->assertSame(
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
            $this->translator
        );

        $listener->setInContextTranslation($event);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
     */
    private function requestWithSetLocaleExpectsNever(): MockObject
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['setLocale'])
            ->getMock();
        $request
            ->expects($this->never())
            ->method('setLocale');

        return $request;
    }
}
