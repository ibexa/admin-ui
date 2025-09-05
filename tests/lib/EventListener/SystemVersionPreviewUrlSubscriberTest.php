<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\SystemVersionPreviewUrlSubscriber;
use Ibexa\Contracts\AdminUi\Event\ResolveVersionPreviewUrlEvent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SystemVersionPreviewUrlSubscriberTest extends TestCase
{
    private const EXAMPLE_PREVIEW_URL = '/example';
    private const EXAMPLE_CONTENT_ID = 99;
    private const EXAMPLE_VERSION_NO = 42;
    private const EXAMPLE_LANGUAGE_CODE = 'eng-EN';
    private const EXAMPLE_SITE_ACCESS = 'example_site_access';

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                ResolveVersionPreviewUrlEvent::class,
            ],
            array_keys(SystemVersionPreviewUrlSubscriber::getSubscribedEvents())
        );
    }

    public function testOnSystemVersionPreviewIsSkippedIfUrlHasBeenResolved(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $event = new ResolveVersionPreviewUrlEvent(
            $this->createMock(VersionInfo::class),
            $this->createMock(Language::class),
            $this->createMock(Location::class),
            $this->createMock(SiteAccess::class)
        );
        $event->setPreviewUrl(self::EXAMPLE_PREVIEW_URL);

        $subscriber = new SystemVersionPreviewUrlSubscriber($urlGenerator);
        $subscriber->onResolveVersionPreviewUrl($event);

        self::assertEquals(self::EXAMPLE_PREVIEW_URL, $event->getPreviewUrl());
    }

    public function testOnSystemVersionPreview(): void
    {
        $contentInfo = $this->createMock(ContentInfo::class);
        $contentInfo->method('getId')->willReturn(self::EXAMPLE_CONTENT_ID);

        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo->method('getVersionNo')->willReturn(self::EXAMPLE_VERSION_NO);
        $versionInfo->method('getContentInfo')->willReturn($contentInfo);

        $language = $this->createMock(Language::class);
        $language->method('getLanguageCode')->willReturn(self::EXAMPLE_LANGUAGE_CODE);

        $siteAccess = $this->createMock(SiteAccess::class);
        $siteAccess->name = self::EXAMPLE_SITE_ACCESS;

        $event = new ResolveVersionPreviewUrlEvent(
            $versionInfo,
            $language,
            $this->createMock(Location::class),
            $siteAccess
        );

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->with(
                'ibexa.version.preview',
                [
                    'contentId' => self::EXAMPLE_CONTENT_ID,
                    'versionNo' => self::EXAMPLE_VERSION_NO,
                    'language' => self::EXAMPLE_LANGUAGE_CODE,
                    'siteAccessName' => self::EXAMPLE_SITE_ACCESS,
                ]
            )
            ->willReturn(self::EXAMPLE_PREVIEW_URL);

        $subscriber = new SystemVersionPreviewUrlSubscriber($urlGenerator);
        $subscriber->onResolveVersionPreviewUrl($event);

        self::assertEquals(self::EXAMPLE_PREVIEW_URL, $event->getPreviewUrl());
    }
}
