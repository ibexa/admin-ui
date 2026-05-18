import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class SectionsPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openList(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/section/list`);
  }

  async clickCreateSection(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create a new Section' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' })).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async fillSectionForm(name: string, identifier?: string): Promise<void> {
    await this.fillField('Name', name);
    if (identifier) {
      await this.fillField('Identifier', identifier);
    }
  }

  async save(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Save' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async discard(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Discard' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async discardChanges(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Discard changes' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async editFromList(sectionName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: sectionName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('a.ibexa-btn--ghost').filter({ hasText: '' }).last();
    // Find edit button (not assign button) - edit has title="Edit"
    const editLink = row.locator('a[title="Edit"], a.ibexa-btn--ghost.ibexa-btn--no-text').last();
    await editLink.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  async startAssigningFromList(sectionName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: sectionName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const assignBtn = row.locator('button.ibexa-btn--open-udw, [data-form-action*="assign"]').first();
    await assignBtn.click();
    await this.page.locator('.m-ud').waitFor({ state: 'visible', timeout: 10_000 });
  }

  async startAssigningFromDetailPage(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Assign content' })
      .or(this.page.locator('.ibexa-btn--open-udw')).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForTimeout(500);
  }

  private async _clickContextAction(label: RegExp): Promise<void> {
    const btn = this.page.locator('.ibexa-btn:not([data-bs-dismiss]), button:not([data-bs-dismiss])').filter({ hasText: label }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
  }

  async openSectionDetails(name: string): Promise<void> {
    await this.findTableRowAcrossPages(name);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const link = row.locator('a').first();
    await link.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  async assertOnSectionListPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/section\/list/);
  }

  async assertOnSectionPage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertSectionInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertSectionNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertSectionAttributes(name: string, identifier: string): Promise<void> {
    await expect(this.page.getByText(name).first()).toBeVisible();
    await expect(this.page.getByText(identifier).first()).toBeVisible();
  }

  async assertSectionHasNoContentItems(): Promise<void> {
    // Empty section page shows an empty-state message or zero data rows
    const emptyMsg = this.page.locator('.ibexa-table__empty-table-text, .ibexa-empty-table, td.ibexa-table__cell--no-content');
    const hasEmptyMsg = await emptyMsg.first().isVisible({ timeout: 3_000 }).catch(() => false);
    if (hasEmptyMsg) return;
    // Fallback: last table should have no rows with actual content (td cells with text)
    const tables = this.page.locator('.ibexa-table');
    const tableCount = await tables.count();
    if (tableCount > 1) {
      const contentTable = tables.last();
      const dataRows = contentTable.locator('tbody tr');
      const count = await dataRows.count();
      if (count === 1) {
        // Could be an empty-state row — verify it has no link/content
        const firstRowText = (await dataRows.first().textContent() ?? '').trim();
        if (firstRowText === '' || firstRowText.toLowerCase().includes('no content') || firstRowText.toLowerCase().includes('empty')) return;
      }
      await expect(dataRows).toHaveCount(0, { timeout: 5_000 });
    }
  }

  async assertSectionContentItemsCount(name: string, count: number): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const countCell = row.locator('td').nth(2);
    await expect(countCell).toContainText(String(count));
  }

  async assertSectionHasAssignedItems(sectionName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: sectionName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const checkbox = row.locator('input[type="checkbox"]').first();
    await expect(checkbox).toBeDisabled();
  }

  async assertSectionHasNoAssignedItems(sectionName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: sectionName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const checkbox = row.locator('input[type="checkbox"]').first();
    await expect(checkbox).not.toBeDisabled();
  }

  async assertContentItemsInSection(items: Array<{ Name: string }>): Promise<void> {
    for (const item of items) {
      await this.assertTableRowPresent(item.Name);
    }
  }

  async deleteSection(name: string): Promise<void> {
    await this.checkTableRow(name);
    const deleteBtn = this.page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' }).first();
    await deleteBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await deleteBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('domcontentloaded');
  }

  async deleteSectionFromDetailPage(): Promise<void> {
    await this._clickContextAction(/Delete/);
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('domcontentloaded');
  }

  async startEditingFromDetailPage(): Promise<void> {
    await this._clickContextAction(/Edit/);
    await this.page.waitForLoadState('domcontentloaded');
  }
}
