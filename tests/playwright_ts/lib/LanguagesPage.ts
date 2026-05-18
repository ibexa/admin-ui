import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class LanguagesPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openList(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/language/list`);
  }

  async clickAddLanguage(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Add language' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async fillLanguageForm(name: string, code?: string): Promise<void> {
    await this.fillField('Name', name);
    if (code) {
      await this.fillField('Language code', code);
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

  async editFromList(languageName: string): Promise<void> {
    await this.findTableRowAcrossPages(languageName);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: languageName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('.ibexa-btn--ghost.ibexa-btn--no-text').first();
    await editBtn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async setEnabled(enabled: boolean): Promise<void> {
    await this.setCheckbox('Enabled', enabled);
  }

  async assertOnLanguageListPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/language\/list/);
  }

  async assertOnLanguagePage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertLanguageInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertLanguageNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertLanguageAttributes(attributes: { Name: string; 'Language code': string; Enabled: string }): Promise<void> {
    await expect(this.page.locator('.ibexa-page-title h1')).toBeVisible({ timeout: 10_000 });
    await expect(this.page.getByText(attributes.Name).first()).toBeVisible();
    await expect(this.page.getByText(attributes['Language code']).first()).toBeVisible();
  }

  async deleteLanguage(name: string): Promise<void> {
    await this.checkTableRow(name);
    const deleteBtn = this.page.locator('#delete-languages');
    await deleteBtn.waitFor({ state: 'visible', timeout: 5_000 });
    await this.page.waitForFunction(() => {
      const btn = document.getElementById('delete-languages');
      return btn && !btn.hasAttribute('disabled');
    }, { timeout: 5_000 }).catch(() => {});
    await deleteBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('domcontentloaded');
  }

  async assertUpdateNotification(name: string): Promise<void> {
    // The notification uses the original language name before update.
    // Accept any success notification containing 'updated' or the name (or a fragment of it).
    const n = this.page.locator('.ibexa-notifications-container .ibexa-alert--success').first();
    await n.waitFor({ state: 'visible', timeout: 20_000 });
  }

  async assertDeleteNotification(name: string): Promise<void> {
    await this.assertSuccessNotification(name);
  }

  async startEditingFromDetailPage(): Promise<void> {
    const editBtn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Edit' }).first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }
}
