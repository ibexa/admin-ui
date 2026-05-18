import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class TrashPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async open(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/trash/list`);
  }

  async assertNotEmpty(): Promise<void> {
    const rows = this.page.locator('.ibexa-table__row').filter({ hasText: /\S/ });
    const count = await rows.count();
    expect(count).toBeGreaterThan(0);
  }

  async assertEmpty(): Promise<void> {
    const emptyEl = this.page.locator('.ibexa-table__empty-table-text')
      .or(this.page.getByText('Trash is empty'))
      .or(this.page.getByText('No items'));
    await emptyEl.first().waitFor({ state: 'visible', timeout: 10_000 });
  }

  async emptyTrash(): Promise<void> {
    const emptyBtn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Empty Trash' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Empty' })).first();
    await emptyBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await emptyBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('networkidle');
  }

  async assertItemInTrash(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertItemNotInTrash(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async deleteFromTrash(items: string[]): Promise<void> {
    for (const item of items) {
      await this.checkTableRow(item);
    }
    const deleteBtn = this.page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' }).first();
    await deleteBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await deleteBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('networkidle');
  }

  async restoreFromTrash(items: string[]): Promise<void> {
    for (const item of items) {
      await this.checkTableRow(item);
    }
    // Find "Restore" button (not "Restore in a new location") by matching inner text exactly
    const restoreBtn = this.page.locator('button').filter({ hasNotText: 'in a new location' }).filter({ hasText: 'Restore' }).first();
    await restoreBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await restoreBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  async restoreUnderNewLocation(items: string[], newLocationPath: string): Promise<void> {
    for (const item of items) {
      await this.checkTableRow(item);
    }
    const restoreBtn = this.page.locator('button').filter({ hasText: 'Restore in a new location' })
      .or(this.page.locator('button.ibexa-btn--open-udw')).first();
    await restoreBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await restoreBtn.click();
    await this.page.waitForTimeout(1000);
  }

  async searchInTrash(query: string): Promise<void> {
    const url = this.page.url().split('?')[0];
    await this.page.goto(`${url}?trash_search[content_name]=${encodeURIComponent(query)}`);
    await this.page.waitForLoadState('networkidle');
  }

  async filterByContentType(contentTypeName: string): Promise<void> {
    const select = this.page.locator('select').first();
    await select.waitFor({ state: 'visible', timeout: 10_000 });
    await select.selectOption({ label: contentTypeName });
    await this.page.waitForLoadState('networkidle');
  }
}
