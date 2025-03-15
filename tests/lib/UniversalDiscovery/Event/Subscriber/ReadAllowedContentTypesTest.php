<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber\ReadAllowedContentTypes;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReadAllowedContentTypesTest extends TestCase
{
    private const EXAMPLE_LIMITATIONS = [/* Some limitations */];

    private const SUPPORTED_CONFIG_NAMES = ['richtext_embed', 'richtext_embed_image'];

    private const ALLOWED_CONTENT_TYPES_IDS = [2, 4];
    private const ALLOWED_CONTENT_TYPES = ['article', 'folder'];

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $permissionResolver;

    /** @var \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $permissionChecker;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $contentTypeService;

    /** @var \Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber\ReadAllowedContentTypes */
    private ReadAllowedContentTypes $subscriber;

    protected function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);

        $this->subscriber = new ReadAllowedContentTypes(
            $this->permissionResolver,
            $this->permissionChecker,
            $this->contentTypeService
        );
    }

    public function testUdwConfigResolveOnUnsupportedConfigName(): void
    {
        $this->permissionResolver->method('hasAccess')->with('content', 'read')->willReturn(true);
        $this->permissionChecker->expects(self::never())->method('getRestrictions');
        $this->contentTypeService->expects(self::never())->method('loadContentTypeList');

        $event = $this->createConfigResolveEvent('unsupported_config_name');

        $this->subscriber->onUdwConfigResolve($event);

        $expectedConfig = [
            'allowed_content_types' => null,
        ];

        self::assertEquals($expectedConfig, $event->getConfig());
    }

    public function testUdwConfigResolveWhenThereIsNoContentReadLimitations(): void
    {
        $this->permissionResolver->method('hasAccess')->with('content', 'read')->willReturn(true);
        $this->permissionChecker->expects(self::never())->method('getRestrictions');
        $this->contentTypeService->expects(self::never())->method('loadContentTypeList');

        $this->assertConfigurationResolvingResult([
            'allowed_content_types' => null,
        ]);
    }

    public function testUdwConfigResolveWhenThereIsNoContentReadLimitationsAndNoAccess(): void
    {
        $this->permissionResolver->method('hasAccess')->with('content', 'read')->willReturn(false);
        $this->permissionChecker->expects(self::never())->method('getRestrictions');
        $this->contentTypeService->expects(self::never())->method('loadContentTypeList');

        $this->assertConfigurationResolvingResult([
            'allowed_content_types' => [null],
        ]);
    }

    public function testUdwConfigResolveWhenThereAreContentReadLimitations(): void
    {
        $this->permissionResolver
            ->method('hasAccess')
            ->with('content', 'read')
            ->willReturn(self::EXAMPLE_LIMITATIONS);

        $this->permissionChecker
            ->method('getRestrictions')
            ->with(self::EXAMPLE_LIMITATIONS, ContentTypeLimitation::class)
            ->willReturn(self::ALLOWED_CONTENT_TYPES_IDS);

        $this->contentTypeService
            ->method('loadContentTypeList')
            ->with(self::ALLOWED_CONTENT_TYPES_IDS)
            ->willReturn($this->createContentTypeListMock(self::ALLOWED_CONTENT_TYPES));

        $this->assertConfigurationResolvingResult([
            'allowed_content_types' => self::ALLOWED_CONTENT_TYPES,
        ]);
    }

    private function assertConfigurationResolvingResult(?array $expectedConfiguration): void
    {
        foreach (self::SUPPORTED_CONFIG_NAMES as $configName) {
            $event = $this->createConfigResolveEvent($configName);

            $this->subscriber->onUdwConfigResolve($event);

            self::assertEquals(
                $expectedConfiguration,
                $event->getConfig()
            );
        }
    }

    private function createConfigResolveEvent(string $configName = 'richtext_embed'): ConfigResolveEvent
    {
        $event = new ConfigResolveEvent();
        $event->setConfigName($configName);

        return $event;
    }

    private function createContentTypeListMock(array $identifiers): array
    {
        return array_map(function (string $identifier): MockObject {
            $contentType = $this->createMock(ContentType::class);
            $contentType->method('__get')->with('identifier')->willReturn($identifier);

            return $contentType;
        }, $identifiers);
    }
}
