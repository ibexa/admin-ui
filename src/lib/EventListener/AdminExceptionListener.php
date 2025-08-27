<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use JMS\TranslationBundle\Annotation\Ignore;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Throwable;
use Twig\Environment;
use Twig\Error\RuntimeError;

class AdminExceptionListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param array<mixed> $siteAccessGroups
     * @param \Psr\Log\LogLevel::* $logLevel
     */
    public function __construct(
        protected Environment $twig,
        protected NotificationHandlerInterface $notificationHandler,
        protected TagRenderer $encoreTagRenderer,
        protected EntrypointLookupCollectionInterface $entrypointLookupCollection,
        protected array $siteAccessGroups,
        protected string $rootDir,
        protected string $kernelEnvironment,
        private readonly string $logLevel
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->kernelEnvironment !== 'prod') {
            return;
        }

        if (!$this->isAdminException($event)) {
            return;
        }

        $response = new Response();
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $code = $response->getStatusCode();

        // map exception to UI notification
        $this->notificationHandler->error(/** @Ignore */
            $this->getNotificationMessage($exception)
        );
        $this->logger->log($this->logLevel, $exception->getMessage(), [
            'exception' => $exception,
        ]);

        if ($exception instanceof RuntimeError) {
            // If exception is coming from the template where encore already
            // rendered resources it would result in no CSS/JS on error page.
            // Thus we reset TagRenderer to prevent it from breaking error page.
            $this->encoreTagRenderer->reset();
            $this->entrypointLookupCollection->getEntrypointLookup('ibexa')->reset();
        }

        switch ($code) {
            case 404:
                $content = $this->twig->render('@ibexadesign/ui/error_page/404.html.twig');
                break;
            case 403:
                $content = $this->twig->render('@ibexadesign/ui/error_page/403.html.twig');
                break;
            default:
                $content = $this->twig->render('@ibexadesign/ui/error_page/unknown.html.twig');
                break;
        }

        $response->setContent($content);
        $event->setResponse($response);
    }

    private function isAdminException(ExceptionEvent $event): bool
    {
        $request = $event->getRequest();

        /** @var \Ibexa\Core\MVC\Symfony\SiteAccess $siteAccess */
        $siteAccess = $request->get('siteaccess', new SiteAccess('default'));

        return in_array(
            $siteAccess->name,
            $this->siteAccessGroups[IbexaAdminUiBundle::ADMIN_GROUP_NAME]
        );
    }

    private function getNotificationMessage(Throwable $exception): string
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getMessage();
        }

        $file = new SplFileInfo($exception->getFile());
        $line = $exception->getLine();

        $relativePathname = (new Filesystem())->makePathRelative($file->getPath(), $this->rootDir) . $file->getFilename();

        $message = $exception->getMessage();

        return sprintf('%s [in %s:%d]', $message, $relativePathname, $line);
    }
}
