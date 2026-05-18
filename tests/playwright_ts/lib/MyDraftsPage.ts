import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class MyDraftsPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async open(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/contentdraft/list`);
  }

  async assertDraftPresent(title: string): Promise<void> {
    await this.assertTableRowPresent(title);
  }

  async assertDraftAbsent(title: string): Promise<void> {
    await this.assertTableRowAbsent(title);
  }

  async deleteDraft(title: string): Promise<void> {
    await this.checkTableRow(title);
    const deleteBtn = this.page.locator('#delete-drafts, .ibexa-context-menu button').filter({ hasText: 'Delete' })
      .or(this.page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' })).first();
    await deleteBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await deleteBtn.dispatchEvent('click');
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('networkidle');
  }

  async assertDraftDeleted(title: string): Promise<void> {
    await this.assertTableRowAbsent(title);
  }

  async editDraft(title: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: title }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editLink = row.locator('a').first();
    await editLink.click();
    await this.page.waitForLoadState('networkidle');
  }
}
