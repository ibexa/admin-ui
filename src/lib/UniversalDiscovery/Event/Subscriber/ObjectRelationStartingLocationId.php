<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ObjectRelationStartingLocationId implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        $configName = $event->getConfigName();
        if ('single' !== $configName && 'multiple' !== $configName) {
            return;
        }

        $context = $event->getContext();
        if (
            !isset($context['type'])
            || 'object_relation' !== $context['type']
        ) {
            return;
        }

        $config = $event->getConfig();

        $startingLocationId = $context['starting_location_id'] ?? $config['starting_location_id'];
        $rootDefaultLocation = $context['root_default_location'] ?? false;

        $config['starting_location_id'] = $startingLocationId;
        if ($rootDefaultLocation) {
            $config['root_location_id'] = $startingLocationId;
        }

        $event->setConfig($config);
    }
}
