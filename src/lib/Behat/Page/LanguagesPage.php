<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

class LanguagesPage extends Page
{
    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private TableInterface $table;

    private Dialog $dialog;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder, Dialog $dialog)
    {
        parent::__construct($session, $router);
        $this->table = $tableBuilder->newTable()->build();
        $this->dialog = $dialog;
    }

    public function editLanguage(string $languageName): void
    {
        $this->table->getTableRow(['Name' => $languageName])->edit();
    }

    public function deleteLanguage(string $languageName): void
    {
        $this->table->getTableRow(['Name' => $languageName])->select();
        $this->getHTMLPage()->find($this->getLocator('deleteButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function isLanguageOnTheList(string $languageName): bool
    {
        return $this->table->hasElement(['Name' => $languageName]);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('pageTitle'))->assert()->textEquals('Languages');
        $this->getHTMLPage()->find($this->getLocator('listHeader'))->assert()->textContains('List');
    }

    public function getName(): string
    {
        return 'Languages';
    }

    protected function getRoute(): string
    {
        return 'language/list';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('listHeader', '.ibexa-table-header .ibexa-table-header__headline, header .ibexa-table__headline, header h5'),
            new VisibleCSSLocator('deleteButton', '.ibexa-icon--trash,button[data-bs-original-title^="Delete"]'),
        ];
    }
}
