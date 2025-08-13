<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Table;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Pagination;
use Ibexa\Behat\Browser\Locator\CSSLocator;
use Ibexa\Behat\Browser\Locator\LocatorCollection;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Core\Base\Exceptions\BadStateException;

final class TableBuilder
{
    private LocatorCollection $locators;

    private ?bool $buildInProgress = null;

    public function __construct(
        private readonly Session $session,
        private readonly TableRowFactory $tableRowFactory,
        private readonly Pagination $pagination
    ) {
        $this->locators = new LocatorCollection([]);
    }

    public function newTable(): self
    {
        if ($this->buildInProgress) {
            throw new BadStateException('buildInProgress', 'A Table building process is already in progress. Please finish it before starting a new one.');
        }

        $this->buildInProgress = true;

        $this->locators = new LocatorCollection([
            new VisibleCSSLocator('empty', '.ibexa-table__empty-table-cell'),
            new VisibleCSSLocator('columnHeader', '.ibexa-table__header-cell,th'),
            new VisibleCSSLocator('row', 'tr'),
            new VisibleCSSLocator('cell', 'div.ibexa-table__cell:nth-of-type(%d),td:nth-of-type(%d)'),
            new VisibleCSSLocator('parent', '.ibexa-table'),
        ]);

        return $this;
    }

    public function build(): TableInterface
    {
        if (!$this->buildInProgress) {
            throw new BadStateException('buildInProgress', 'Please call "newTable()" before building a Table object');
        }

        $this->buildInProgress = false;

        return new Table($this->session, $this->tableRowFactory, $this->pagination, $this->locators);
    }

    public function withRowLocator(CSSLocator $locator): self
    {
        $rowLocator = new CSSLocator('row', $locator->getSelector());
        $this->locators->replace($rowLocator);

        return $this;
    }

    public function withTableCell(CSSLocator $locator): self
    {
        $rowLocator = new CSSLocator('cell', $locator->getSelector());
        $this->locators->replace($rowLocator);

        return $this;
    }

    public function withParentLocator(CSSLocator $locator): self
    {
        $parentLocator = new CSSLocator('parent', $locator->getSelector());
        $this->locators->replace($parentLocator);

        return $this;
    }

    public function withEmptyLocator(CSSLocator $locator): self
    {
        $emptyLocator = new CSSLocator('empty', $locator->getSelector());
        $this->locators->replace($emptyLocator);

        return $this;
    }

    public function withColumnLocator(CSSLocator $locator): self
    {
        $columnLocator = new CSSLocator('columnHeader', $locator->getSelector());
        $this->locators->replace($columnLocator);

        return $this;
    }
}
