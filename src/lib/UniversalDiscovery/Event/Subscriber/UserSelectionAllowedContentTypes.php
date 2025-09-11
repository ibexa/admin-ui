<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class UserSelectionAllowedContentTypes implements EventSubscriberInterface
{
    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        $config = $event->getConfig();

        if (!in_array($event->getConfigName(), ['single_user', 'multiple_user'])) {
            return;
        }

        $config['allowed_content_types'] = $this->configResolver->getParameter(
            'user_content_type_identifier'
        );

        $event->setConfig($config);
    }
}
