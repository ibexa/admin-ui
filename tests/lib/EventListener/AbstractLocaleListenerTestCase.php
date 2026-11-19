<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\EventListener\LocaleAwareListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\Translation\Translator;

abstract class AbstractLocaleListenerTestCase extends TestCase
{
    protected const string ADMIN_SITEACCESS = 'admin_siteaccess';

    /**
     * Since symfony/http-kernel 7.4.17, LocaleAwareListener restores the locale every
     * locale-aware service had before a sub-request. The locale set by the listener under
     * test must therefore be known to all of them, LocaleSwitcher included, or it is lost
     * after the first sub-request.
     *
     * @param callable(Translator, LocaleSwitcher): (callable(RequestEvent): void) $createRequestHandler
     */
    protected function assertLocaleIsKeptAfterSubRequest(
        callable $createRequestHandler,
        string $expectedLocale
    ): void {
        $httpKernel = $this->createStub(HttpKernelInterface::class);
        $translator = new Translator('en_GB');
        $localeSwitcher = new LocaleSwitcher('en_GB', [$translator]);
        $mainRequest = self::createAdminRequest('en_GB');
        $requestStack = new RequestStack([$mainRequest]);
        $localeAwareListener = new LocaleAwareListener([$translator, $localeSwitcher], $requestStack);
        $handleRequest = $createRequestHandler($translator, $localeSwitcher);

        $mainRequestEvent = new RequestEvent($httpKernel, $mainRequest, HttpKernelInterface::MAIN_REQUEST);
        $localeAwareListener->onKernelRequest($mainRequestEvent);
        $handleRequest($mainRequestEvent);

        $subRequest = self::createAdminRequest($expectedLocale);
        $requestStack->push($subRequest);
        $localeAwareListener->onKernelRequest(
            new RequestEvent($httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $localeAwareListener->onKernelFinishRequest(
            new FinishRequestEvent($httpKernel, $subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $requestStack->pop();

        self::assertSame($expectedLocale, $mainRequest->getLocale());
        self::assertSame($expectedLocale, $translator->getLocale());
        self::assertSame($expectedLocale, $localeSwitcher->getLocale());
    }

    private static function createAdminRequest(string $locale): Request
    {
        $request = new Request();
        $request->setLocale($locale);
        $request->attributes->set('siteaccess', new SiteAccess(self::ADMIN_SITEACCESS));

        return $request;
    }
}
