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
use PHPUnit\Framework\Assert;

final class ObjectStateGroupsPage extends Page
{
    private TableInterface $table;

    public function __construct(
        readonly Session $session,
        readonly Router $router,
        readonly TableBuilder $tableBuilder,
        private readonly Dialog $dialog
    ) {
        parent::__construct($session, $router);

        $this->table = $tableBuilder->newTable()->build();
    }

    public function isObjectStateGroupOnTheList(string $objectStateGroupName): bool
    {
        return $this->table->hasElement(['Object state group name' => $objectStateGroupName]);
    }

    public function editObjectStateGroup(string $objectStateGroupName): void
    {
        $this->table->getTableRow(['Object state group name' => $objectStateGroupName])->edit();
    }

    public function createObjectStateGroup(): void
    {
        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    public function deleteObjectStateGroup(string $objectStateGroupName): void
    {
        $this->table->getTableRow(['Object state group name' => $objectStateGroupName])->select();
        $this->getHTMLPage()->find($this->getLocator('deleteButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertEquals(
            'Object state groups',
            $this->getHTMLPage()->find($this->getLocator('pageTitle'))->getText()
        );
    }

    public function getName(): string
    {
        return 'Object State groups';
    }

    protected function getRoute(): string
    {
        return '/state/groups';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('createButton', '.ibexa-icon--create'),
            new VisibleCSSLocator('deleteButton', '#delete-object-state-groups'),
        ];
    }
}
