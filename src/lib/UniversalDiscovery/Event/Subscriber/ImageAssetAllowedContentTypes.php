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

final class ImageAssetAllowedContentTypes implements EventSubscriberInterface
{
    private AssetMapper $assetMapper;

    public function __construct(AssetMapper $assetMapper)
    {
        $this->assetMapper = $assetMapper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        if ($event->getConfigName() !== 'image_asset') {
            return;
        }

        $config = $event->getConfig();
        $config['allowedContentTypes'][] = $this->assetMapper->getContentTypeIdentifier();

        $event->setConfig($config);
    }
}
