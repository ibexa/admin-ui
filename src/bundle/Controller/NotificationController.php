<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
use Ibexa\AdminUi\Pagination\Pagerfanta\NotificationAdapter;
use Ibexa\Bundle\AdminUi\View\IbexaPagerfantaView;
use Ibexa\Bundle\AdminUi\View\Template\IbexaPagerfantaTemplate;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Notification\Renderer\Registry;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    protected Registry $registry;

    protected TranslatorInterface $translator;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        NotificationService $notificationService,
        Registry $registry,
        TranslatorInterface $translator,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationService = $notificationService;
        $this->registry = $registry;
        $this->translator = $translator;
        $this->configResolver = $configResolver;
    }

    public function getNotificationsAction(int $offset, int $limit): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notificationList = $this->notificationService->loadNotifications($offset, $limit);
            $response->setData([
                'pending' => $this->notificationService->getPendingNotificationCount(),
                'total' => $notificationList->totalCount,
                'notifications' => $notificationList->items,
            ]);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderNotificationsPageAction(int $page): Response
    {
        $pagerfanta = new Pagerfanta(
            new NotificationAdapter($this->notificationService)
        );
        $pagerfanta->setMaxPerPage($this->configResolver->getParameter('pagination.notification_limit'));
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        $notifications = '';
        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            if ($this->registry->hasRenderer($notification->type)) {
                $renderer = $this->registry->getRenderer($notification->type);
                $notifications .= $renderer->render($notification);
            }
        }

        $routeGenerator = function ($page): string {
            return $this->generateUrl('ibexa.notifications.render.page', [
                'page' => $page,
            ]);
        };

        $pagination = (new IbexaPagerfantaView(new IbexaPagerfantaTemplate()))->render($pagerfanta, $routeGenerator);

        return new Response($this->render('@ibexadesign/account/notifications/list.html.twig', [
            'page' => $page,
            'pagination' => $pagination,
            'notifications' => $notifications,
            'notifications_count_interval' => $this->configResolver->getParameter('notification_count.interval'),
            'pager' => $pagerfanta,
        ])->getContent());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function countNotificationsAction(): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $response->setData([
                'pending' => $this->notificationService->getPendingNotificationCount(),
                'total' => $this->notificationService->getNotificationCount(),
            ]);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * We're not able to establish two-way stream (it requires additional
     * server service for websocket connection), so we need a way to mark notification
     * as read. AJAX call is fine.
     */
    public function markNotificationAsReadAction(string $notificationId): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notification = $this->notificationService->getNotification((int)$notificationId);

            $this->notificationService->markNotificationAsRead($notification);

            $data = ['status' => 'success'];

            if ($this->registry->hasRenderer($notification->type)) {
                $url = $this->registry->getRenderer($notification->type)->generateUrl($notification);

                if ($url) {
                    $data['redirect'] = $url;
                }
            }

            $response->setData($data);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $response;
    }
}
