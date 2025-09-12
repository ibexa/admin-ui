<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\AdminUi\Behat\Component\TrashSearch;
use Ibexa\AdminUi\Behat\Component\UniversalDiscoveryWidget;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use PHPUnit\Framework\Assert;

final class TrashPage extends Page
{
    private TableInterface $table;

    /** @var \Ibexa\AdminUi\Behat\Component\TrashSearch */
    private TrashSearch $trashSearch;

    public function __construct(
        readonly Session $session,
        readonly Router $router,
        private readonly UniversalDiscoveryWidget $universalDiscoveryWidget,
        private readonly Dialog $dialog,
        private readonly ContentActionsMenu $contentActionsMenu,
        readonly TableBuilder $tableBuilder,
        TrashSearch $trashSearch
    ) {
        parent::__construct($session, $router);

        $this->table = $tableBuilder->newTable()->build();
        $this->trashSearch = $trashSearch;
    }

    public function hasElement(string $itemType, string $itemName): bool
    {
        return $this->table->hasElement(['Name' => $itemName, 'Content type' => $itemType]);
    }

    public function isEmpty(): bool
    {
        return $this->table->isEmpty();
    }

    public function restoreSelectedNewLocation(string $pathToContent): void
    {
        $this->getHTMLPage()->find($this->getLocator('restoreUnderNewLocationButton'))->click();
        $this->universalDiscoveryWidget->verifyIsLoaded();
        $this->universalDiscoveryWidget->selectContent($pathToContent);
        $this->universalDiscoveryWidget->confirm();
    }

    public function emptyTrash(): void
    {
        $this->contentActionsMenu->clickButton('Empty Trash');
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function deleteSelectedItems(): void
    {
        $this->getHTMLPage()->find($this->getLocator('trashButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function select(array $parameters): void
    {
        $this->table->getTableRow($parameters)->select();
    }

    public function searchByText(string $searchQuery): void
    {
        $this->trashSearch->submitSearchText($searchQuery);
        $this->trashSearch->confirmSearch();
    }

    public function filterByContentType(string $contentType): void
    {
        $this->trashSearch->filterByContentType($contentType);
    }

    public function filterBySection(string $section): void
    {
        $this->trashSearch->filterBySection($section);
    }

    public function filterByContentItemCreator(string $contentItemCreator): void
    {
        $this->trashSearch->filterByContentItemCreator($contentItemCreator);
    }

    public function restoreSelectedItems()
    {
        $this->getHTMLPage()->find($this->getLocator('restoreButton'))->click();
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertEquals(
            'Trash',
            $this->getHTMLPage()->find($this->getLocator('pageTitle'))->getText()
        );
    }

    public function getName(): string
    {
        return 'Trash';
    }

    protected function getRoute(): string
    {
        return 'trash/list';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('restoreButton', '#trash_item_restore_restore'),
            new VisibleCSSLocator('trashButton', '#delete-trash-items'),
            new VisibleCSSLocator('restoreUnderNewLocationButton', '#trash_item_restore_location_select_content'),
        ];
    }
}
