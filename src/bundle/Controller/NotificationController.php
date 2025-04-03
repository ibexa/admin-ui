<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Notification\NotificationRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Pagination\Pagerfanta\NotificationAdapter;
use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use Ibexa\Bundle\AdminUi\Form\Type\SearchType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Notification\Renderer\Registry;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends Controller
{
    /** @var \Ibexa\Contracts\Core\Repository\NotificationService */
    protected $notificationService;

    /** @var \Ibexa\Core\Notification\Renderer\Registry */
    protected $registry;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    protected $translator;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

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
        } catch (\Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    public function renderNotificationsPageAction(Request $request, int $page): Response
    {
        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            $query = $this->buildQuery($data);
        }

        $pagerfanta = new Pagerfanta(
            new NotificationAdapter($this->notificationService, $query ?? null)
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

        $template = $request->attributes->get('template', '@ibexadesign/account/notifications/list.html.twig');

        $deleteNotificationsForm = $this->formFactory->deleteNotification(
            $this->createNotificationRemoveData($pagerfanta)
        );

        return $this->render($template, [
            'notifications' => $notifications,
            'notifications_count_interval' => $this->configResolver->getParameter('notification_count.interval'),
            'pager' => $pagerfanta,
            'search_form' => $searchForm->createView(),
            'form_remove' => $deleteNotificationsForm->createView(),
        ]);
    }

    private function buildQuery(SearchQueryData $data): ?string
    {
        return $data->getQuery();
    }

    private function createSearchForm(): FormInterface
    {
        return $this->createForm(SearchType::class);
    }

    private function createNotificationRemoveData(Pagerfanta $pagerfanta): NotificationRemoveData
    {
        $notificationIds = [];

        foreach ($pagerfanta->getCurrentPageResults() as $notification) {
            $notificationIds[$notification->id] = false;
        }

        return new NotificationRemoveData($notificationIds);
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
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
            $response->setData([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            $response->setStatusCode(404);
        }

        return $response;
    }

    public function markNotificationAsUnreadAction(Request $request, mixed $notificationId): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $notification = $this->notificationService->getNotification((int)$notificationId);

            $this->notificationService->markNotificationAsUnread($notification);

            $data = ['status' => 'success'];

            $response->setData($data);
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
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
            $result = $this->submitHandler->handle($form, function (NotificationRemoveData $data) {
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
