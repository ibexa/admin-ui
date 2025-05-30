<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\RequestListener;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListenerTest extends TestCase
{
    private Request&MockObject $request;

    private HttpKernelInterface&MockObject $httpKernel;

    private RequestListener $requestListener;

    private RequestEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestListener = new RequestListener(['some_name' => ['group_1']]);

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getSession', 'hasSession'])
            ->getMock();

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);

        $this->event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    public function testOnKernelRequestDeniedAccess(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The route is not allowed in the current SiteAccess');

        $this->request->attributes->set('siteaccess', new SiteAccess('some_name'));
        $this->request->attributes->set('siteaccess_group_whitelist', ['group_2', 'group_3']);

        $this->requestListener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestAllowAccessWithSubRequest(): void
    {
        $this->expectNotToPerformAssertions();

        $this->event = new RequestEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::SUB_REQUEST
        );

        $this->requestListener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestAllowAccessWithoutSiteAccess(): void
    {
        $this->expectNotToPerformAssertions();

        $this->request->attributes->set('siteaccess', 'not_siteaccess_object');

        $this->requestListener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestAllowAccessWithoutGroupWhitelist(): void
    {
        $this->expectNotToPerformAssertions();

        $this->request->attributes->set('siteaccess_group_whitelist', null);

        $this->requestListener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestAllowAccessWhenGroupMatch(): void
    {
        $this->expectNotToPerformAssertions();

        $this->request->attributes->set('siteaccess', new SiteAccess('some_name'));
        $this->request->attributes->set('siteaccess_group_whitelist', ['group_1', 'group_2']);

        $this->requestListener->onKernelRequest($this->event);
    }

    public function testSubscribedEvents(): void
    {
        self::assertSame([
            KernelEvents::REQUEST => ['onKernelRequest', 13],
        ], $this->requestListener::getSubscribedEvents());
    }
}
