<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\DetailsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class DetailsTabVisibilityTest extends AbstractTabVisibilityTestCase
{
    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        return new DetailsTab(
            $this->createMock(Environment::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(SectionService::class),
            $this->createMock(DatasetFactory::class),
            $this->createMock(FormFactoryInterface::class),
            $this->createMock(PermissionResolver::class),
            $userSettingService,
            $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [FocusMode::FOCUS_MODE_ON, [], false];
        yield 'focus mode off' => [FocusMode::FOCUS_MODE_OFF, [], true];
    }
}
