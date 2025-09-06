<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Contracts\AdminUi\Event\ResolveVersionPreviewUrlEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SystemVersionPreviewUrlSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResolveVersionPreviewUrlEvent::class => ['onResolveVersionPreviewUrl', -100],
        ];
    }

    public function onResolveVersionPreviewUrl(ResolveVersionPreviewUrlEvent $event): void
    {
        if ($event->getPreviewUrl() !== null) {
            // Do not override already set preview URL
            return;
        }

        $previewUrl = $this->urlGenerator->generate(
            'ibexa.version.preview',
            [
                'contentId' => $event->getVersionInfo()->getContentInfo()->getId(),
                'versionNo' => $event->getVersionInfo()->getVersionNo(),
                'language' => $event->getLanguage()->getLanguageCode(),
                'siteAccessName' => $event->getSiteAccess()->name,
            ],
        );

        $event->setPreviewUrl($previewUrl);
    }
}
