<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

final class MyDraftsPage extends Page
{
    private TableInterface $table;
    private Dialog $dialog;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder, Dialog $dialog)
    {
        parent::__construct($session, $router);
        $this->table = $tableBuilder->newTable()->build();
        $this->dialog = $dialog;
    }

    public function verifyIsLoaded(): void
    {
    }

    public function deleteDraft(string $draftName): void
    {
        $this->table->getTableRow(['Name' => $draftName])->select();

        $this->getHTMLPage()->find($this->getLocator('deleteButton'))->click();
        $this->dialog->confirm();
    }

    public function doSeeDraft(string $draftName): bool
    {
        if (!$this->table->getTableRow(['Name' => $draftName])->canBeSelected())
        {
            return true;
        }
        return false;
    }

    public function clickEditDraft(string $draftName): void
    {
        $this->getHTMLPage()->find($this->getLocator('editButton'))->click();
    }
    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('deleteButton', '#confirm-content_remove_remove'),
            new VisibleCSSLocator('editButton', '.ibexa-btn--content-draft-edit'),
        ];
    }

    public function getName(): string
    {
        return 'MyDrafts';
    }

    protected function getRoute(): string
    {
        return 'contentdraft/list';
    }
}
