<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\Contracts\AdminUi\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AllNotificationsController extends Controller
{
    public function renderAllNotificationsPageAction(Request $request, int $page): Response
    {
        return $this->forward(
            NotificationController::class . '::renderNotificationsPageAction',
            [
                'page' => $page,
                'template' => '@ibexadesign/account/notifications/list_all.html.twig',
                'render_all' => true,
            ]
        );
    }
}
