<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\NormalizedLanguageCodeListener;
use Ibexa\Contracts\AdminUi\Request\ContentLanguageContext;
use Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class NormalizedLanguageCodeListenerTest extends TestCase
{
    private NormalizedLanguageCodeListener $listener;

    /**
     * @var \Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ContentLanguageCodeResolverInterface $resolver;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(ContentLanguageCodeResolverInterface::class);
        $this->listener = new NormalizedLanguageCodeListener($this->resolver);
    }

    public function testLanguageCodeIsSetFromResolver(): void
    {
        $request = new Request();
        $this->resolver
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn('eng-GB');

        $this->listener->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('eng-GB', $request->attributes->get(ContentLanguageContext::ATTRIBUTE_NAME));
    }

    public function testNormalizedLanguageCodeHasPriorityOverResolver(): void
    {
        $request = new Request([], [], [ContentLanguageContext::ATTRIBUTE_NAME => 'eng-GB']);
        $this->resolver
            ->expects(self::never())
            ->method('resolve');

        $this->listener->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('eng-GB', $request->attributes->get(ContentLanguageContext::ATTRIBUTE_NAME));
    }

    public function testNullValueFromResolverIsIgnored(): void
    {
        $request = new Request();
        $this->resolver
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn(null);

        $this->listener->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertNull($request->attributes->get(ContentLanguageContext::ATTRIBUTE_NAME));
    }

    public function testLanguageCodeIsNotChangedOnSubRequest(): void
    {
        $request = new Request();
        $this->resolver
            ->expects(self::never())
            ->method('resolve');

        $this->listener->onKernelRequest($this->createEvent($request, HttpKernelInterface::SUB_REQUEST));

        self::assertNull($request->attributes->get(ContentLanguageContext::ATTRIBUTE_NAME));
    }

    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 12]],
            NormalizedLanguageCodeListener::getSubscribedEvents()
        );
    }

    private function createEvent(Request $request, int $requestType): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType
        );
    }
}
