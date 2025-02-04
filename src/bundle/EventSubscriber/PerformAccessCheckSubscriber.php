<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\EventSubscriber;

use Ibexa\Contracts\AdminUi\Controller\Controller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

/**
 * Performs access check to backoffice controllers.
 */
final class PerformAccessCheckSubscriber implements EventSubscriberInterface
{
    public function onControllerArgumentsEvent(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        if (is_array($controller) && $controller[0] instanceof Controller) {
            $controller[0]->performAccessCheck();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => 'onControllerArgumentsEvent',
        ];
    }
}
