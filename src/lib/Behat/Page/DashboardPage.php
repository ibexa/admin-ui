<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

class DashboardPage extends Page
{
    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    protected $table;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder)
    {
        parent::__construct($session, $router);
        $this->table = $tableBuilder->newTable()->withParentLocator($this->getLocator('table'))->build();
    }

    public function switchTab(string $tableName, string $tabName)
    {
        if ($this->getActiveTabName($tableName) == $tabName) {
            return;
        } else {
            $this->getHTMLPage()
            ->findAll($this->getLocator('tableSelector'))->getByCriterion(new ChildElementTextCriterion($this->getLocator('tableTitle'), $tableName))
            ->findAll($this->getLocator('tableTab'))->getByCriterion(new ElementTextCriterion($tabName))
            ->click()
            ;
        }
    }

    public function getActiveTabName(string $tableName): string
    {
        return $this->getHTMLPage()
            ->findAll($this->getLocator('tableSelector'))->getByCriterion(new ChildElementTextCriterion($this->getLocator('tableTitle'), $tableName))
            ->find($this->getLocator('activeTabLink'))->getText();
    }

    public function isListEmpty(): bool
    {
        return $this->table->isEmpty();
    }

    public function editDraft(string $contentDraftName)
    {
        $this->table->getTableRow(['Name' => $contentDraftName])->edit();
    }

    public function isDraftOnList(string $draftName): bool
    {
        return $this->table->hasElement(['Name' => $draftName]);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('pageTitle'))->assert()->textEquals('My dashboard');
        $this->getHTMLPage()->findAll($this->getLocator('tableTitle'))
            ->getByCriterion(new ElementTextCriterion('My content'))
            ->assert()->isVisible();
    }

    public function createContent(): void
    {
        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    public function getName(): string
    {
        return 'Dashboard';
    }

    protected function getRoute(): string
    {
        return 'dashboard';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('tableSelector', '.ibexa-card'),
            new VisibleCSSLocator('tableTitle', '.ibexa-card__title'),
            new VisibleCSSLocator('tableTab', '.ibexa-tabs .nav-item'),
            new VisibleCSSLocator('pageTitle', '.ibexa-header-wrapper h1'),
            new VisibleCSSLocator('table', '#ibexa-tab-dashboard-my-my-drafts'),
            new VisibleCSSLocator('createButton', '.ibexa-btn--cotf-create'),
            new VisibleCSSLocator('activeTabLink', '.ibexa-tabs__link.active'),
        ];
    }
}
