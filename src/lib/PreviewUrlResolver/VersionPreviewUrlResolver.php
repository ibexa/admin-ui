<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\PreviewUrlResolver;

use Ibexa\Contracts\AdminUi\Event\ResolveVersionPreviewUrlEvent;
use Ibexa\Contracts\AdminUi\Exception\UnresolvedPreviewUrlException;
use Ibexa\Contracts\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VersionPreviewUrlResolver implements VersionPreviewUrlResolverInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function resolveUrl(
        VersionInfo $versionInfo,
        Location $location,
        Language $language,
        SiteAccess $siteAccess
    ): string {
        $event = $this->eventDispatcher->dispatch(
            new ResolveVersionPreviewUrlEvent(
                $versionInfo,
                $language,
                $location,
                $siteAccess
            )
        );

        $previewUrl = $event->getPreviewUrl();
        if ($previewUrl === null) {
            throw new UnresolvedPreviewUrlException(
                sprintf(
                    'Preview URL for content id = %d and version no = %d could not be resolved.',
                    $versionInfo->getContentInfo()->getId(),
                    $versionInfo->getVersionNo()
                )
            );
        }

        return $previewUrl;
    }
}
