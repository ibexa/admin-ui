<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber\ContentCreate;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\TestCase;

class ContentCreateTest extends TestCase
{
    public const ALLOWED_CONTENT_TYPE_IDENTIFIER = 'lorem';
    private const ALLOWED_LANGUAGE_CODE = 'eng-GB';
    private const ALLOWED_CONTENT_TYPE_ID = 1;

    /** @var \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface|PHPUnit\Framework\MockObject\MockObject */
    private $permissionChecker;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService|PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver|PHPUnit\Framework\MockObject\MockObject */
    private $permissionResolver;

    public function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    /**
     * @dataProvider createTab
     */
    public function testUdwConfigResolveWithCreateTab(array $config): void
    {
        $event = new ConfigResolveEvent();
        $event->setConfigName('create');
        $event->setConfig($config);

        $subscriber = $this->getSubscriberWithRestrictions();
        $subscriber->onUdwConfigResolve($event);

        $addedConfig = [
            'content_on_the_fly' => [
                'allowed_languages' => [self::ALLOWED_LANGUAGE_CODE],
            ],
        ];

        $expectedConfig = $config + $addedConfig;

        self::assertEquals($expectedConfig, $event->getConfig());
    }

    /**
     * @dataProvider withoutCreateTab
     */
    public function testUdwConfigResolveWithoutCreateTab($config): void
    {
        $event = new ConfigResolveEvent();
        $event->setConfigName('some_config');
        $event->setConfig($config);

        $subscriber = $this->getSubscriberWithRestrictions();
        $subscriber->onUdwConfigResolve($event);

        self::assertEquals($config, $event->getConfig());
    }

    public function createTab(): array
    {
        return [
            'all_tabs' => [
                [
                    'visible_tabs' => [],
                    'allowed_content_types' => [
                        self::ALLOWED_CONTENT_TYPE_IDENTIFIER,
                    ],
                ],
            ],
            'explicit_create_tab' => [
                [
                    'visible_tabs' => ['tab', 'create'],
                    'allowed_content_types' => [
                        self::ALLOWED_CONTENT_TYPE_IDENTIFIER,
                    ],
                ],
            ],
        ];
    }

    public function withoutCreateTab(): array
    {
        return [
            'one_tab' => [
                [
                    'visible_tabs' => ['tab'],
                    'content_on_the_fly' => [
                        'allowed_languages' => [self::ALLOWED_LANGUAGE_CODE],
                    ],
                    'allowed_content_types' => [
                        self::ALLOWED_CONTENT_TYPE_IDENTIFIER,
                    ],
                ],
            ],
            'many_tabs' => [
                [
                    'visible_tabs' => ['tab', 'other_tab'],
                    'content_on_the_fly' => [
                        'allowed_languages' => [self::ALLOWED_LANGUAGE_CODE],
                    ],
                    'allowed_content_types' => [
                        self::ALLOWED_CONTENT_TYPE_IDENTIFIER,
                    ],
                ],
            ],
        ];
    }

    private function getSubscriberWithRestrictions(): ContentCreate
    {
        $this->permissionResolver
            ->method('hasAccess')
            ->with('content', 'create')
            ->willReturn([]);

        $this->permissionChecker
            ->method('getRestrictions')
            ->willReturnMap([
                [[], ContentTypeLimitation::class, [self::ALLOWED_CONTENT_TYPE_ID]],
                [[], LanguageLimitation::class, [self::ALLOWED_LANGUAGE_CODE]],
            ]);

        $this->contentTypeService
            ->method('loadContentType')
            ->with(self::ALLOWED_CONTENT_TYPE_ID)
            ->willReturn(new ContentType(['identifier' => self::ALLOWED_CONTENT_TYPE_IDENTIFIER]));

        return new ContentCreate(
            $this->permissionResolver,
            $this->permissionChecker,
            $this->contentTypeService
        );
    }
}
