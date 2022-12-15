<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\Core\FieldType\ImageAsset\AssetMapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImageAssetDefaultLocationId implements EventSubscriberInterface
{
    /** @var \Ibexa\Core\FieldType\ImageAsset\AssetMapper */
    private $assetMapper;

    /**
     * @param \Ibexa\Core\FieldType\ImageAsset\AssetMapper $assetMapper
     */
    public function __construct(AssetMapper $assetMapper)
    {
        $this->assetMapper = $assetMapper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    /**
     * @param \Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent $event
     */
    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        if ($event->getConfigName() !== 'image_asset') {
            return;
        }

        $config = $event->getConfig();
        $config['starting_location_id'] = $this->assetMapper->getParentLocationId();

        $event->setConfig($config);
    }
}

class_alias(ImageAssetDefaultLocationId::class, 'EzSystems\EzPlatformAdminUi\UniversalDiscovery\Event\Subscriber\ImageAssetDefaultLocationId');
