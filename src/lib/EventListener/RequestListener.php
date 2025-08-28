<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RequestListener implements EventSubscriberInterface
{
    /**
     * @param array<mixed> $groupsBySiteAccess
     */
    public function __construct(private array $groupsBySiteAccess)
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * Returns the event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 13],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $requestAttributes = $event->getRequest()->attributes;

        $siteAccess = $requestAttributes->get('siteaccess');
        $allowedGroups = $requestAttributes->get('siteaccess_group_whitelist');

        if (!$siteAccess instanceof SiteAccess || empty($allowedGroups)) {
            return;
        }

        $allowedGroups = (array)$allowedGroups;

        foreach ($this->groupsBySiteAccess[$siteAccess->name] as $group) {
            if (in_array($group, $allowedGroups, true)) {
                return;
            }
        }

        throw new NotFoundHttpException('The route is not allowed in the current SiteAccess');
    }
}
