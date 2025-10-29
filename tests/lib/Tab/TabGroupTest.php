<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab;

use Ibexa\AdminUi\Tab\TabGroup;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use PHPUnit\Framework\TestCase;

class TabGroupTest extends TestCase
{
    public function testAddTab()
    {
        $tabIdentifier = 'tab_identifier';

        $tab = $this->createMock(TabInterface::class);
        $tab->expects(self::once())
            ->method('getIdentifier')
            ->willReturn($tabIdentifier);

        $tabGroup = new TabGroup('group_name');

        self::assertCount(0, $tabGroup->getTabs());

        $tabGroup->addTab($tab);

        self::assertCount(1, $tabGroup->getTabs());
    }

    public function testAddTabWithSameIdentifier()
    {
        $tabIdentifier = 'tab_identifier';

        $tab = $this->createMock(TabInterface::class);
        $tab->expects(self::once())
            ->method('getIdentifier')
            ->willReturn($tabIdentifier);

        $tabWithSameIdentifier = $this->createMock(TabInterface::class);
        $tabWithSameIdentifier->expects(self::once())
            ->method('getIdentifier')
            ->willReturn($tabIdentifier);

        $tabGroup = new TabGroup('group_name');
        $tabGroup->addTab($tab);

        self::assertCount(1, $tabGroup->getTabs());
        $tabGroup->addTab($tabWithSameIdentifier);
        self::assertCount(1, $tabGroup->getTabs());
    }

    public function testRemoveTab()
    {
        $tabIdentifier = 'tab_identifier';

        $tab = $this->createMock(TabInterface::class);
        $tab->expects(self::once())
            ->method('getIdentifier')
            ->willReturn($tabIdentifier);

        $tabGroup = new TabGroup('group_name');
        $tabGroup->addTab($tab);

        self::assertCount(1, $tabGroup->getTabs());
        $tabGroup->removeTab($tabIdentifier);
        self::assertCount(0, $tabGroup->getTabs());
    }

    public function testRemoveTabWhenNotExist()
    {
        $tabIdentifier = 'tab_identifier';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not find a tab identified as "%s".', $tabIdentifier));

        $tab = $this->createMock(TabInterface::class);
        $tab->expects(self::never())->method(self::anything());

        $tabGroup = new TabGroup('group_name');

        self::assertCount(0, $tabGroup->getTabs());
        $tabGroup->removeTab($tabIdentifier);
    }
}

class_alias(TabGroupTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Tab\TabGroupTest');
