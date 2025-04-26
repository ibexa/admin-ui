<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Tab;

use Ibexa\AdminUi\Tab\TabGroup;
use Ibexa\AdminUi\Tab\TabRegistry;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TabRegistryTest extends TestCase
{
    private string $groupName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupName = 'group_name';
    }

    public function testGetTabsByGroupNameWhenGroupDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not find the requested group named "%s". Did you tag the service?', $this->groupName));

        $tabRegistry = new TabRegistry();
        $tabRegistry->getTabsByGroupName($this->groupName);
    }

    public function testGetTabsByGroupName(): void
    {
        $twig = $this->createMock(Environment::class);
        $translator = $this->createMock(TranslatorInterface::class);

        $tabs = [
            $this->createTab('tab1', $twig, $translator),
            $this->createTab('tab1', $twig, $translator),
        ];

        $tabGroup = $this->createTabGroup($this->groupName, $tabs);
        $tabRegistry = new TabRegistry();
        $tabRegistry->addTabGroup($tabGroup);

        self::assertSame($tabs, $tabRegistry->getTabsByGroupName($this->groupName));
    }

    public function testGetTabFromGroup(): void
    {
        $twig = $this->createMock(Environment::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $tab1 = $this->createTab('tab1', $twig, $translator);
        $tabs = [$tab1, $this->createTab('tab2', $twig, $translator)];

        $tabRegistry = new TabRegistry();
        $tabGroup = $this->createTabGroup($this->groupName, $tabs);
        $tabRegistry->addTabGroup($tabGroup);

        self::assertSame($tab1, $tabRegistry->getTabFromGroup('tab1', $this->groupName));
    }

    public function testGetTabFromGroupWhenGroupDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not find the requested group named "%s". Did you tag the service?', $this->groupName));

        $tabRegistry = new TabRegistry();
        $tabRegistry->getTabFromGroup('tab1', $this->groupName);
    }

    public function testGetTabFromGroupWhenTabDoesNotExist(): void
    {
        $tabName = 'tab1';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not find the requested tab "%s" from group "%s". Did you tag the service?', $tabName, $this->groupName));

        $tabs = [];
        $tabRegistry = new TabRegistry();
        $tabGroup = $this->createTabGroup($this->groupName, $tabs);
        $tabRegistry->addTabGroup($tabGroup);
        $tabRegistry->getTabFromGroup($tabName, $this->groupName);
    }

    public function testAddTabGroup(): void
    {
        $tabRegistry = new TabRegistry();
        $tabGroup = $this->createTabGroup();
        $tabRegistry->addTabGroup($tabGroup);

        self::assertSame($tabGroup, $tabRegistry->getTabGroup('lorem'));
    }

    public function testAddTabGroupWithSameIdentifier(): void
    {
        $tabGroup = $this->createTabGroup($this->groupName);
        $tabGroupWithSameIdentifier = $this->createTabGroup($this->groupName);

        $tabRegistry = new TabRegistry();
        $tabRegistry->addTabGroup($tabGroup);

        self::assertSame($tabGroup, $tabRegistry->getTabGroup($this->groupName));
        $tabRegistry->addTabGroup($tabGroupWithSameIdentifier);
        self::assertSame($tabGroupWithSameIdentifier, $tabRegistry->getTabGroup($this->groupName));
    }

    public function testAddTabToExistingGroup(): void
    {
        $twig = $this->createMock(Environment::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $existingTab = $this->createTab('existing_tab', $twig, $translator);
        $addedTab = $this->createTab('added_tab', $twig, $translator);

        $tabRegistry = new TabRegistry();
        $tabGroup = $this->createTabGroup($this->groupName, [$existingTab]);
        $tabRegistry->addTabGroup($tabGroup);

        self::assertCount(1, $tabRegistry->getTabsByGroupName($this->groupName));
        $tabRegistry->addTab($addedTab, $this->groupName);
        self::assertCount(2, $tabRegistry->getTabsByGroupName($this->groupName));
    }

    public function testAddTabToNonExistentGroup(): void
    {
        $twig = $this->createMock(Environment::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $addedTab = $this->createTab('added_tab', $twig, $translator);

        $tabRegistry = new TabRegistry();
        $tabRegistry->addTab($addedTab, $this->groupName);

        self::assertCount(1, $tabRegistry->getTabsByGroupName($this->groupName));
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\Tab\TabInterface[] $tabs
     */
    private function createTabGroup(string $name = 'lorem', array $tabs = []): TabGroup
    {
        return new TabGroup($name, $tabs);
    }

    private function createTab(string $name, Environment $twig, TranslatorInterface $translator): TabInterface
    {
        return new class($name, $twig, $translator) extends AbstractTab {
            protected string $name;

            protected Environment $twig;

            protected TranslatorInterface $translator;

            public function __construct(string $name, Environment $twig, TranslatorInterface $translator)
            {
                parent::__construct($twig, $translator);
                $this->name = $name;
            }

            public function getIdentifier(): string
            {
                return 'identifier';
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function renderView(array $parameters): string
            {
                return '';
            }
        };
    }
}
