<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use DateTimeInterface;
use Ibexa\AdminUi\Form\Data\Notification\NotificationSelectionData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Notification\SearchType;
use Ibexa\AdminUi\Pagination\Pagerfanta\NotificationAdapter;
use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\NotificationQuery;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Notification\Renderer\Registry;
use InvalidArgumentException;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    protected Registry $registry;

    protected TranslatorInterface $translator;

    private ConfigResolverInterface $configResolver;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private TranslatableNotificationHandlerInterface $notificationHandler;

    public function __construct(
        NotificationService $notificationService,
        Registry $registry,
        TranslatorInterface $translator,
        ConfigResolverInterface $configResolver,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        TranslatableNotificationHandlerInterface $notificationHandler
    ) {
        $this->notificationService = $notificationService;
        $this->registry = $registry;
        $this->translator = $translator;
        $this->configResolver = $configResolver;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * @param callable(): JsonResponse $callback
     */
    private function handleJsonErrors(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (NotFoundException $exception) {
            return new JsonResponse([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ], 404);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'status' => 'failed',
                'error' => 'Unexpected error occurred.',
            ], 500);
        }
    }

    public function getNotificationsAction(Request $request, int $offset, int $limit): JsonResponse
    {
        return $this->handleJsonErrors(function () use ($offset, $limit) {
            $notificationList = $this->notificationService->loadNotifications($offset, $limit);

            return new JsonResponse([
                'pending' => $this->notificationService->getPendingNotificationCount(),
                'total' => $notificationList->totalCount,
                'notifications' => $notificationList->items,
            ]);
        });
    }

    public function renderNotificationsPageAction(Request $request, int $page): Response
    {
        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);

        $query = $this->getNotificationQuery($request, $searchForm);

        $pagerfanta = new Pagerfanta(
            new NotificationAdapter($this->notificationService, $query)
        );

        $limit = $this->configResolver->getParameter('pagination.notification_limit');
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        $notifications = $this->renderNotificationsPage($pagerfanta);

        $deleteForm = $this->formFactory->deleteNotification($this->createNotificationSelectionData($pagerfanta));

        $template = $request->attributes->get('template', '@ibexadesign/account/notifications/list.html.twig');

        return $this->render($template, [
            'notifications' => $notifications,
            'sidebarNotifications' => $this->renderNotifications($this->notificationService->loadNotifications(0, $limit)->items),
            'notifications_count_interval' => $this->configResolver->getParameter('notification_count.interval'),
            'pager' => $pagerfanta,
            'search_form' => $searchForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<SearchQueryData|null> $searchForm
     */
    private function getNotificationQuery(Request $request, FormInterface $searchForm): NotificationQuery
    {
        $session = $request->getSession();

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            $session->set('notifications_filters', $data);

            return $this->buildQuery($data);
        }

        $data = $session->get('notifications_filters');
        if ($data !== null) {
            $searchForm->setData($data);

            return $this->buildQuery($data);
        }

        return new NotificationQuery();
    }

    /**
     * Renders notifications from any iterable source or Pagerfanta page.
     *
     * @param iterable<\Ibexa\Contracts\Core\Repository\Values\Notification\Notification> $notifications
     *
     * @return string[]
     */
    private function renderNotifications(iterable $notifications): array
    {
        $result = [];
        foreach ($notifications as $notification) {
            if ($this->registry->hasRenderer($notification->type)) {
                $result[] = $this->registry->getRenderer($notification->type)->render($notification);
            }
        }

        return $result;
    }

    /**
     * Renders current page of Pagerfanta notifications.
     *
     * @param \Pagerfanta\Pagerfanta<\Ibexa\Contracts\Core\Repository\Values\Notification\Notification> $pagerfanta
     *
     * @return string[]
     */
    private function renderNotificationsPage(Pagerfanta $pagerfanta): array
    {
        return $this->renderNotifications($pagerfanta->getCurrentPageResults());
    }

    private function buildQuery(?SearchQueryData $data): NotificationQuery
    {
        if ($data === null) {
            return new NotificationQuery([]);
        }

        $criteria = [];

        if ($data->getType()) {
            $criteria[] = new Criterion\Type($data->getType());
        }

        if (!empty($data->getStatuses())) {
            $criteria[] = new Criterion\Status($data->getStatuses());
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

    /**
     * @param \Pagerfanta\Pagerfanta<\Ibexa\Contracts\Core\Repository\Values\Notification\Notification> $pagerfanta
     */
    private function createNotificationSelectionData(Pagerfanta $pagerfanta): NotificationSelectionData
    {
        $notifications = [];

        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            $notifications[$notification->id] = false;
        }

        return new NotificationSelectionData($notifications);
    }

    public function countNotificationsAction(): JsonResponse
    {
        return $this->handleJsonErrors(fn () => new JsonResponse([
            'pending' => $this->notificationService->getPendingNotificationCount(),
            'total' => $this->notificationService->getNotificationCount(),
        ]));
    }

    /**
     * We're not able to establish two-way stream (it requires additional
     * server service for websocket connection), so * we need a way to mark notification
     * as read. AJAX call is fine.
     */
    public function markNotificationAsReadAction(Request $request, int $notificationId): JsonResponse
    {
        return $this->handleJsonErrors(function () use ($notificationId) {
            $notification = $this->notificationService->getNotification($notificationId);

            $this->notificationService->markNotificationAsRead($notification);

            $data = ['status' => 'success'];

            if ($this->registry->hasRenderer($notification->type)) {
                $url = $this->registry->getRenderer($notification->type)->generateUrl($notification);

                if ($url) {
                    $data['redirect'] = $url;
                }
            }

            return new JsonResponse($data);
        });
    }

    public function markNotificationsAsReadAction(Request $request): JsonResponse
    {
        return $this->handleJsonErrors(function () use ($request) {
            $ids = $request->toArray()['ids'] ?? [];

            if (empty($ids)) {
                throw new InvalidArgumentException('Missing or invalid "ids" parameter.');
            }

            $this->notificationService->markUserNotificationsAsRead($ids);

            return new JsonResponse([
                'status' => 'success',
                'redirect' => $this->generateUrl('ibexa.notifications.render.all'),
            ]);
        });
    }

    public function markAllNotificationsAsReadAction(Request $request): JsonResponse
    {
        return $this->handleJsonErrors(function () {
            $this->notificationService->markUserNotificationsAsRead();

            return new JsonResponse(['status' => 'success']);
        });
    }

    public function markNotificationAsUnreadAction(Request $request, int $notificationId): JsonResponse
    {
        return $this->handleJsonErrors(function () use ($notificationId) {
            $notification = $this->notificationService->getNotification($notificationId);

            $this->notificationService->markNotificationAsUnread($notification);

            return new JsonResponse(['status' => 'success']);
        });
    }

    public function deleteNotificationAction(Request $request, int $notificationId): JsonResponse
    {
        return $this->handleJsonErrors(function () use ($notificationId) {
            $notification = $this->notificationService->getNotification($notificationId);

            $this->notificationService->deleteNotification($notification);

            return new JsonResponse(['status' => 'success']);
        });
    }

    public function deleteNotificationsAction(Request $request): Response
    {
        $form = $this->formFactory->deleteNotification();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->redirectToRoute('ibexa.notifications.render.all');
        }

        if ($form->isValid()) {
            $result = $this->submitHandler->handle(
                $form,
                function (NotificationSelectionData $data): Response {
                    return $this->processDeleteNotifications($data);
                }
            );

            return $result ?? $this->redirectToRoute('ibexa.notifications.render.all');
        }

        $this->notificationHandler->error(
            /** @Desc("An unexpected error occurred while deleting notifications.") */
            'error.unexpected_delete_notifications',
            [],
            'ibexa_notifications'
        );

        return $this->redirectToRoute('ibexa.notifications.render.all');
    }

    private function processDeleteNotifications(NotificationSelectionData $data): RedirectResponse
    {
        foreach (array_keys($data->getNotifications()) as $id) {
            $notification = $this->notificationService->getNotification((int)$id);
            $this->notificationService->deleteNotification($notification);
        }

        return $this->redirectToRoute('ibexa.notifications.render.all');
    }
}

class_alias(NotificationController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\NotificationController');
