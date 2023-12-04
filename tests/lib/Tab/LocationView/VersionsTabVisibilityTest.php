<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Tab\LocationView\VersionsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class VersionsTabVisibilityTest extends AbstractTabVisibilityTestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content&\PHPUnit\Framework\MockObject\MockObject */
    private Content $exampleContent;

    protected function setUp(): void
    {
        $this->exampleContent = $this->createMock(Content::class);
    }

    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        $permissionResolver = $this->createMock(PermissionResolver::class);
        $permissionResolver->method('canUser')->with('content', 'versionread', $this->exampleContent)->willReturn(true);

        return new VersionsTab(
            $this->createMock(Environment::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(DatasetFactory::class),
            $this->createMock(FormFactory::class),
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(PermissionResolver::class),
            $this->createMock(UserService::class),
            $userSettingService,
            $this->createMock(EventDispatcherInterface::class),
        );
    }

    public function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'smart mode' => [
            UserMode::SMART,
            ['content' => $this->exampleContent],
            false,
        ];

        yield 'expert mode' => [
            UserMode::EXPERT,
            ['content' => $this->exampleContent],
            true,
        ];
    }
}
