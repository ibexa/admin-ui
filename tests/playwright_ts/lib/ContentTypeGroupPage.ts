import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class ContentTypeGroupPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openList(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/contenttypegroup/list`);
  }

  async clickCreate(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create a new' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' })).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async fillForm(name: string): Promise<void> {
    await this.fillField('Name', name);
  }

  async save(): Promise<void> {
    await this.clickContextButton('Save');
  }

  async discard(): Promise<void> {
    await this.clickContextButton('Discard');
  }

  async discardChanges(): Promise<void> {
    await this.clickContextButton('Discard changes');
  }

  async editFromList(groupName: string): Promise<void> {
    await this.clickRowEditButton(groupName);
  }

  async assertOnListPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/contenttypegroup\/list/);
  }

  async assertOnGroupPage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertGroupInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertGroupNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertGroupHasNoContentTypes(): Promise<void> {
    const emptyEl = this.page.locator('.ibexa-table__empty-table-text, .ibexa-empty-table')
      .or(this.page.getByText('There are no Content Types'));
    // Check there are no content type rows
    const rows = this.page.locator('.ibexa-table__row').filter({ hasText: /\S/ });
    const count = await rows.count();
    expect(count).toBe(0);
  }

  async assertGroupIsNonEmpty(groupName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: groupName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const checkbox = row.locator('input[type="checkbox"]').first();
    await expect(checkbox).toBeDisabled();
  }

  async deleteFromList(groupName: string): Promise<void> {
    await this.checkTableRow(groupName);
    const deleteBtn = this.page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' }).first();
    await deleteBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await deleteBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('networkidle');
  }
}
