<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Pagination\Pagerfanta\NotificationAdapter;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Notification\Renderer\Registry;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AllNotificationsController extends Controller
{
    /** @var \Ibexa\Contracts\Core\Repository\NotificationService */
    protected $notificationService;

    /** @var \Ibexa\Core\Notification\Renderer\Registry */
    protected $registry;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        NotificationService $notificationService,
        Registry $registry,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationService = $notificationService;
        $this->registry = $registry;
        $this->configResolver = $configResolver;
    }
    public function renderAllNotificationsPageAction(Request $request, int $page): Response
    {
        $pagerfanta = new Pagerfanta(
            new NotificationAdapter($this->notificationService)
        );
        $pagerfanta->setMaxPerPage($this->configResolver->getParameter('pagination.notification_limit'));
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        $notifications = [];
        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            if ($this->registry->hasRenderer($notification->type)) {
                $renderer = $this->registry->getRenderer($notification->type);
                $notifications[] = $renderer->render($notification);
            }
        }

        return $this->forward(
            NotificationController::class . '::renderNotificationsPageAction',
            [
                'page' => $page,
                'notifications' => $notifications,
                'template' => '@ibexadesign/account/notifications/list_all.html.twig',
                'render_all' => true,
            ]
        );
    }
}
