<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use DateTimeInterface;
use Exception;
use Ibexa\AdminUi\Form\Data\Notification\NotificationSelectionData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Pagination\Pagerfanta\NotificationAdapter;
use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use Ibexa\Bundle\AdminUi\Form\Type\SearchType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\Criterion\NotificationQuery;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Notification\Renderer\Registry;
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    protected Registry $registry;

    protected TranslatorInterface $translator;

    private ConfigResolverInterface $configResolver;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    public function __construct(
        NotificationService $notificationService,
        Registry $registry,
        TranslatorInterface $translator,
        ConfigResolverInterface $configResolver,
        FormFactory $formFactory,
        SubmitHandler $submitHandler
    ) {
        $this->notificationService = $notificationService;
        $this->registry = $registry;
        $this->translator = $translator;
        $this->configResolver = $configResolver;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
    }

    public function getNotificationsAction(Request $request, int $offset, int $limit): JsonResponse
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

    public function renderNotificationsPageAction(Request $request, int $page): Response
    {
        $allNotifications = $this->notificationService->loadNotifications(
            new NotificationQuery([], 0, PHP_INT_MAX)
        )->items;

        $notificationTypes = array_unique(
            array_map(
                static fn($notification) => $notification->type,
                $allNotifications
            )
        );
        sort($notificationTypes);

        $searchForm = $this->createForm(SearchType::class, null, [
            'notification_types' => $notificationTypes,
        ]);
        $searchForm->handleRequest($request);

        $query = new NotificationQuery();
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $query = $this->buildQuery($searchForm->getData());
        }

        $query->offset = ($page - 1) * $this->configResolver->getParameter('pagination.notification_limit');
        $query->limit = $this->configResolver->getParameter('pagination.notification_limit');

        $pagerfanta = new Pagerfanta(
            new NotificationAdapter($this->notificationService, $query)
        );
        $pagerfanta->setMaxPerPage($query->limit);
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        $notifications = [];
        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            if ($this->registry->hasRenderer($notification->type)) {
                $notifications[] = $this->registry->getRenderer($notification->type)->render($notification);
            }
        }

        $formData = $this->createNotificationSelectionData($pagerfanta);
        $deleteForm = $this->formFactory->deleteNotification($formData);

        $template = $request->attributes->get('template', '@ibexadesign/account/notifications/list.html.twig');

        return $this->render($template, [
            'notifications' => $notifications,
            'notifications_count_interval' => $this->configResolver->getParameter('notification_count.interval'),
            'pager' => $pagerfanta,
            'search_form' => $searchForm->createView(),
            'form_remove' => $deleteForm->createView(),
        ]);
    }

    private function buildQuery(SearchQueryData $data): NotificationQuery
    {
        $criteria = [];

        if ($data->getType()) {
            $criteria[] = new Criterion\Type($data->getType());
        }

        if (!empty($data->getStatuses())) {
            $statuses = [];
            if (in_array('read', $data->getStatuses(), true)) {
                $statuses[] = 'read';
            }
            if (in_array('unread', $data->getStatuses(), true)) {
                $statuses[] = 'unread';
            }

            if (!empty($statuses)) {
                $criteria[] = new Criterion\Status($statuses);
            }
        }

        $range = $data->getCreatedRange();
        if ($range !== null) {
            $min = $range->getMin() instanceof DateTimeInterface ? $range->getMin() : null;
            $max = $range->getMax() instanceof DateTimeInterface ? $range->getMax() : null;

            if ($min !== null || $max !== null) {
                $criteria[] = new Criterion\DateCreated($min, $max);
            }
        }

        return new NotificationQuery($criteria);
    }

    private function createNotificationSelectionData(Pagerfanta $pagerfanta): NotificationSelectionData
    {
        $notifications = [];

        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            $notifications[$notification->id] = false;
        }

        return new NotificationSelectionData($notifications);
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
     * server service for websocket connection), so * we need a way to mark notification
     * as read. AJAX call is fine.
     */
    public function markNotificationAsReadAction(Request $request, mixed $notificationId): JsonResponse
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

            $response->setStatusCode(404);
        }

        return $response;
    }

    public function markNotificationsAsReadAction(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $ids = $request->toArray()['ids'] ?? [];

            if (!is_array($ids) || empty($ids)) {
                throw new InvalidArgumentException('Missing or invalid "ids" parameter.');
            }

            foreach ($ids as $id) {
                $notification = $this->notificationService->getNotification((int)$id);
                $this->notificationService->markNotificationAsRead($notification);
            }

            $response->setData([
                'status' => 'success',
                'redirect' => $this->generateUrl('ibexa.notifications.render.all'),
            ]);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
            $response->setStatusCode(400);
        }

        return $response;
    }

    public function markAllNotificationsAsReadAction(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notifications = $this->notificationService->loadNotifications(0, PHP_INT_MAX)->items;

            foreach ($notifications as $notification) {
                $this->notificationService->markNotificationAsRead($notification);
            }

            return $response->setData(['status' => 'success']);
        } catch (Exception $exception) {
            return $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ])->setStatusCode(404);
        }
    }

    public function markNotificationAsUnreadAction(Request $request, mixed $notificationId): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notification = $this->notificationService->getNotification((int)$notificationId);

            $this->notificationService->markNotificationAsUnread($notification);

            $data = ['status' => 'success'];

            $response->setData($data);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            $response->setStatusCode(404);
        }

        return $response;
    }

    public function deleteNotificationAction(Request $request, mixed $notificationId): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notification = $this->notificationService->getNotification((int)$notificationId);

            $this->notificationService->deleteNotification($notification);

            $response->setData(['status' => 'success']);
        } catch (Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            $response->setStatusCode(404);
        }

        return $response;
    }

    public function deleteNotificationsAction(Request $request): Response
    {
        $form = $this->formFactory->deleteNotification();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (NotificationSelectionData $data) {
                foreach (array_keys($data->getNotifications()) as $id) {
                    $notification = $this->notificationService->getNotification((int)$id);
                    $this->notificationService->deleteNotification($notification);
                }

                return $this->redirectToRoute('ibexa.notifications.render.all');
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.notifications.render.all');
    }
}

class_alias(NotificationController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\NotificationController');
