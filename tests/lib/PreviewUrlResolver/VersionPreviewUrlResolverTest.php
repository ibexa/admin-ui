<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\PreviewUrlResolver;

use Ibexa\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolver;
use Ibexa\Contracts\AdminUi\Event\ResolveVersionPreviewUrlEvent;
use Ibexa\Contracts\AdminUi\Exception\UnresolvedPreviewUrlException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Ibexa\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolver
 */
final class VersionPreviewUrlResolverTest extends TestCase
{
    private const EXAMPLE_PREVIEW_URL = 'https://example.org/preview/url';
    private const EXAMPLE_CONTENT_ID = 42;
    private const EXAMPLE_VERSION_NO = 7;

    public function testResolvesPreviewUrlSuccessfully(): void
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $location = $this->createMock(Location::class);
        $language = $this->createMock(Language::class);
        $siteAccess = $this->createMock(SiteAccess::class);

        $event = new ResolveVersionPreviewUrlEvent(
            $versionInfo,
            $language,
            $location,
            $siteAccess
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')
            ->with($event)
            ->willReturnCallback(static function (ResolveVersionPreviewUrlEvent $event): ResolveVersionPreviewUrlEvent {
                // Set preview URL as if an event listener did it
                $event->setPreviewUrl(self::EXAMPLE_PREVIEW_URL);

                return $event;
            });

        $resolver = new VersionPreviewUrlResolver($eventDispatcher);
        $result = $resolver->resolveUrl($versionInfo, $location, $language, $siteAccess);

        self::assertSame(self::EXAMPLE_PREVIEW_URL, $result);
    }

    public function testThrowsExceptionWhenPreviewUrlIsNotResolved(): void
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $location = $this->createMock(Location::class);
        $language = $this->createMock(Language::class);
        $siteAccess = $this->createMock(SiteAccess::class);

        $contentInfo = $this->createMock(ContentInfo::class);
        $contentInfo->method('getId')->willReturn(self::EXAMPLE_CONTENT_ID);

        $versionInfo->method('getContentInfo')->willReturn($contentInfo);
        $versionInfo->method('getVersionNo')->willReturn(self::EXAMPLE_VERSION_NO);

        $event = new ResolveVersionPreviewUrlEvent(
            $versionInfo,
            $language,
            $location,
            $siteAccess
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturn($event);

        $resolver = new VersionPreviewUrlResolver($eventDispatcher);

        $this->expectException(UnresolvedPreviewUrlException::class);
        $this->expectExceptionMessage(
            'Preview URL for content id = 42 and version no = 7 could not be resolved.'
        );

        $resolver->resolveUrl($versionInfo, $location, $language, $siteAccess);
    }
}
