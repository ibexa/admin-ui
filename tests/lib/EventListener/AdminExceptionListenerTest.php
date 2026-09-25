<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\AdminExceptionListener;
use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Throwable;
use Twig\Environment;
use Twig\Error\RuntimeError;

final class AdminExceptionListenerTest extends TestCase
{
    private const ADMIN_SITEACCESS = 'admin_siteaccess';
    private const NON_ADMIN_SITEACCESS = 'non_admin_siteaccess';

    /** @var \Twig\Environment&\PHPUnit\Framework\MockObject\MockObject */
    private Environment $twig;

    /** @var \Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface&\PHPUnit\Framework\MockObject\Stub */
    private NotificationHandlerInterface $notificationHandler;

    /** @var \Symfony\WebpackEncoreBundle\Asset\TagRenderer&\PHPUnit\Framework\MockObject\MockObject */
    private TagRenderer $encoreTagRenderer;

    /** @var \Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EntrypointLookupCollectionInterface $entrypointLookupCollection;

    private AdminExceptionListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twig = $this->createMock(Environment::class);
        $this->notificationHandler = $this->createStub(NotificationHandlerInterface::class);
        $this->encoreTagRenderer = $this->createMock(TagRenderer::class);
        $this->entrypointLookupCollection = $this->createMock(EntrypointLookupCollectionInterface::class);

        $this->listener = $this->createListener();
    }

    /**
     * @dataProvider provideNoOpConditions
     */
    public function testOnKernelExceptionIsNoOpWhenGuardConditionFails(
        string $environment,
        int $requestType,
        string $siteaccessName
    ): void {
        $this->twig->expects(self::never())->method('render');

        $event = $this->createExceptionEvent(new NotFoundHttpException(), $requestType, $siteaccessName);
        $this->createListener($environment)->onKernelException($event);

        self::assertNull($event->getResponse());
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2: string}>
     */
    public static function provideNoOpConditions(): iterable
    {
        yield 'non-prod environment' => ['test', HttpKernelInterface::MAIN_REQUEST, self::ADMIN_SITEACCESS];
        yield 'sub-request' => ['prod', HttpKernelInterface::SUB_REQUEST, self::ADMIN_SITEACCESS];
        yield 'non-admin siteaccess' => ['prod', HttpKernelInterface::MAIN_REQUEST, self::NON_ADMIN_SITEACCESS];
    }

    /**
     * @dataProvider provideHttpExceptionsWithDedicatedErrorPages
     *
     * @param array<string, string> $expectedHeaders
     */
    public function testOnKernelExceptionRendersDedicatedErrorPage(
        Throwable $exception,
        int $expectedStatusCode,
        string $expectedTemplate,
        array $expectedHeaders
    ): void {
        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with($expectedTemplate)
            ->willReturn('rendered_error_page_content');

        $event = $this->createExceptionEvent($exception, HttpKernelInterface::MAIN_REQUEST, self::ADMIN_SITEACCESS);
        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame($expectedStatusCode, $response->getStatusCode());
        self::assertSame('rendered_error_page_content', $response->getContent());

        foreach ($expectedHeaders as $name => $value) {
            self::assertSame($value, $response->headers->get($name));
        }
    }

    /**
     * @return iterable<string, array{0: \Throwable, 1: int, 2: string, 3: array<string, string>}>
     */
    public static function provideHttpExceptionsWithDedicatedErrorPages(): iterable
    {
        yield 'not found' => [
            new NotFoundHttpException(),
            Response::HTTP_NOT_FOUND,
            '@ibexadesign/ui/error_page/404.html.twig',
            [],
        ];

        yield 'access denied' => [
            new AccessDeniedHttpException(),
            Response::HTTP_FORBIDDEN,
            '@ibexadesign/ui/error_page/403.html.twig',
            [],
        ];

        yield 'method not allowed' => [
            new MethodNotAllowedHttpException(['POST']),
            Response::HTTP_METHOD_NOT_ALLOWED,
            '@ibexadesign/ui/error_page/405.html.twig',
            ['Allow' => 'POST'],
        ];
    }

    public function testOnKernelExceptionResetsEncoreAssetsForRuntimeError(): void
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects(self::once())->method('reset');

        $this->entrypointLookupCollection
            ->expects(self::once())
            ->method('getEntrypointLookup')
            ->with('ibexa')
            ->willReturn($entrypointLookup);

        $this->encoreTagRenderer->expects(self::once())->method('reset');

        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with('@ibexadesign/ui/error_page/unknown.html.twig')
            ->willReturn('unknown_page_content');

        $event = $this->createExceptionEvent(
            new RuntimeError('broken template'),
            HttpKernelInterface::MAIN_REQUEST,
            self::ADMIN_SITEACCESS
        );
        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    private function createListener(string $environment = 'prod'): AdminExceptionListener
    {
        $listener = new AdminExceptionListener(
            $this->twig,
            $this->notificationHandler,
            $this->encoreTagRenderer,
            $this->entrypointLookupCollection,
            [IbexaAdminUiBundle::ADMIN_GROUP_NAME => [self::ADMIN_SITEACCESS]],
            '/kernel/root',
            $environment,
            'error'
        );
        $listener->setLogger($this->createStub(LoggerInterface::class));

        return $listener;
    }

    private function createExceptionEvent(Throwable $exception, int $requestType, string $siteaccessName): ExceptionEvent
    {
        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess($siteaccessName));

        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            $requestType,
            $exception
        );
    }
}
