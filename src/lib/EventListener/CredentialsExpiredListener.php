<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\Security\Exception\PasswordExpiredException;
use Ibexa\Core\MVC\Symfony\View\LoginFormView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CredentialsExpiredListener implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $siteAccessGroups
     */
    public function __construct(
        private RequestStack $requestStack,
        private array $siteAccessGroups
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MVCEvents::PRE_CONTENT_VIEW => 'onPreContentView',
        ];
    }

    public function onPreContentView(PreContentViewEvent $event): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null || !$this->isAdminSiteAccess($currentRequest)) {
            return;
        }

        $view = $event->getContentView();
        if (!($view instanceof LoginFormView)) {
            return;
        }

        if ($view->getLastAuthenticationException() instanceof PasswordExpiredException) {
            $view->setTemplateIdentifier('@ibexadesign/account/error/credentials_expired.html.twig');
        }
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
