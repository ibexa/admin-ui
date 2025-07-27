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
use Ibexa\AdminUi\UserSetting\FocusMode;
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

    private function getExampleContent(): Content
    {
        if (!isset($this->exampleContent)) {
            $this->exampleContent = $this->createMock(Content::class);
        }

        return $this->exampleContent;
    }

    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        $permissionResolver = $this->createMock(PermissionResolver::class);
        $permissionResolver->method('canUser')->with('content', 'versionread', $this->getExampleContent())->willReturn(true);

        return new VersionsTab(
            $this->createMock(Environment::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(DatasetFactory::class),
            $this->createMock(FormFactory::class),
            $this->createMock(UrlGeneratorInterface::class),
            $permissionResolver,
            $this->createMock(UserService::class),
            $userSettingService,
            $this->createMock(EventDispatcherInterface::class),
        );
    }

    public function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [
            FocusMode::FOCUS_MODE_ON,
            ['content' => $this->getExampleContent()],
            false,
        ];

        yield 'focus mode off' => [
            FocusMode::FOCUS_MODE_OFF,
            ['content' => $this->getExampleContent()],
            true,
        ];
    }
}
