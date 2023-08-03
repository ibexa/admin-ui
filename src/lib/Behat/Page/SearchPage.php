<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\AdminUi\Behat\Component\UpperMenu;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

class SearchPage extends Page
{
    private TableInterface $table;

    private UpperMenu $upperMenu;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder, UpperMenu $upperMenu)
    {
        parent::__construct($session, $router);
        $this->table = $tableBuilder
            ->newTable()
            ->build();
        $this->upperMenu = $upperMenu;
    }

    public function search(string $contentItemName): void
    {
        $this->upperMenu->search($contentItemName);

        $this->getHTMLPage()->find($this->getLocator('inputField'))->setValue($contentItemName);
        $this->getHTMLPage()->find($this->getLocator('buttonSearch'))->click();
        $this->verifyIsLoaded();
        $this->getHTMLPage()->find($this->getLocator('table'))->assert()->isVisible();
    }

    public function isElementInResults(array $elementData): bool
    {
        return $this->table->hasElement($elementData);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('filtersHeader'))->assert()->textEquals('Filters');
    }

    public function getName(): string
    {
        return 'Search';
    }

    protected function getRoute(): string
    {
        return '/search';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('table', '.ibexa-search'),
            new VisibleCSSLocator('filtersHeader', '.ibexa-search-form__filters .ibexa-filters__title'),
        ];
    }
}
