<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\Table;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use LogicException;

/**
 * Reads the "Open drafts" table on the Versions tab (content/tab/versions/tab.html.twig),
 * following the same {@see \Ibexa\AdminUi\Behat\Component\Table\Table}/TableBuilder convention
 * {@see \Ibexa\AdminUi\Behat\Component\DraftConflictDialog} already uses to read a version
 * table, rather than hand-rolling row iteration.
 *
 * That section, headline included, is only rendered at all when the content item has at least
 * one draft (`show_drafts_table` in the template), so counting its rows doubles as the way to
 * prove no draft was left behind: zero drafts means the whole section is simply absent.
 *
 * The rendered markup gives the draft/published/archived version tables no distinguishing id or
 * CSS class of their own (all three can end up sharing the same `ibexa-table--draft-conflict`
 * modifier - see `table_component.html.twig`'s `table_class|default(...)`), only their headline
 * text differs ("Open drafts" vs "Published version" vs "Archived versions"). Table/TableBuilder
 * itself has no way to select "the table following this headline" - Element exposes no sibling
 * navigation - so the headline text is used once, only to decide whether a drafts section exists
 * at all; once that is established, the drafts table is always the first
 * `.ibexa-table-header + .ibexa-scrollable-wrapper` pair in the tab (drafts is always rendered
 * before published/archived when present), and Table/TableBuilder does the rest.
 */
final class VersionsList extends Component
{
    private const string DRAFTS_HEADLINE = 'Open drafts';

    private Table $draftsTable;

    public function __construct(
        readonly Session $session,
        TableBuilder $tableBuilder
    ) {
        parent::__construct($session);

        $draftsTable = $tableBuilder->newTable()
            ->withParentLocator(new VisibleCSSLocator(
                'draftsTableParent',
                '#ibexa-tab-location-view-versions .ibexa-table-header + .ibexa-scrollable-wrapper table'
            ))
            ->build();

        // TableBuilder::build() is declared to return TableInterface, but always constructs a
        // Table (see TableBuilder::build()); getColumnValues() below is only on the concrete
        // class, not the interface, so this is here to fail loudly instead of silently, not to
        // silence a type error.
        if (!$draftsTable instanceof Table) {
            throw new LogicException(sprintf('Expected %s, got %s.', Table::class, $draftsTable::class));
        }

        $this->draftsTable = $draftsTable;
    }

    public function getDraftVersionCount(): int
    {
        if (!$this->hasOpenDraftsSection()) {
            return 0;
        }

        return count($this->draftsTable->getColumnValues(['Version']));
    }

    public function verifyIsLoaded(): void
    {
    }

    private function hasOpenDraftsSection(): bool
    {
        return $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll($this->getLocator('headline'))
            ->filterBy(new ElementTextCriterion(self::DRAFTS_HEADLINE))
            ->any();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('headline', '#ibexa-tab-location-view-versions .ibexa-table-header__headline'),
        ];
    }
}
