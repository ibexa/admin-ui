import { Page, expect } from '@playwright/test';

export class AdminUiPage {
  constructor(protected page: Page) {}

  protected async navigateTo(url: string): Promise<void> {
    await this.page.goto(url);
    await this.page.waitForLoadState('networkidle');
  }

  async waitForAdminReady(): Promise<void> {
    await this.page.locator('.ibexa-main-container').waitFor({ state: 'visible', timeout: 10_000 });
  }

  async clickContextButton(label: string): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu').locator('.ibexa-btn').filter({ hasText: label }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async assertPageTitle(title: string): Promise<void> {
    const el = this.page.locator('.ibexa-page-title h1');
    await el.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(el).toContainText(title);
  }

  async assertSuccessNotification(text: string): Promise<void> {
    const n = this.page.locator(
      '.ibexa-notifications-container .ibexa-alert--success, [role="alert"]',
    ).filter({ hasText: text }).first();
    await n.waitFor({ state: 'visible', timeout: 20_000 });
    await expect(n).toContainText(text);
  }

  async assertErrorNotification(text: string): Promise<void> {
    const n = this.page.locator(
      '.ibexa-notifications-container .ibexa-alert--danger, [role="alert"]',
    ).filter({ hasText: text }).first();
    await n.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(n).toContainText(text);
  }

  async assertTableRowPresent(name: string): Promise<void> {
    await this.findTableRowAcrossPages(name);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(row).toContainText(name);
  }

  async assertTableRowAbsent(name: string): Promise<void> {
    await this.page.waitForLoadState('networkidle');
    const rows = this.page.locator('.ibexa-table__row').filter({ hasText: name });
    await expect(rows).toHaveCount(0, { timeout: 15_000 });
  }

  async clickRowEditButton(rowName: string): Promise<void> {
    await this.findTableRowAcrossPages(rowName);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: rowName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('.ibexa-btn--ghost.ibexa-btn--no-text').first();
    await editBtn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async checkTableRow(rowName: string): Promise<void> {
    await this.findTableRowAcrossPages(rowName);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: rowName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const checkbox = row.locator('input[type="checkbox"]').first();
    await checkbox.check();
  }

  async fillField(label: string, value: string): Promise<void> {
    const input = this.page.getByLabel(label, { exact: false });
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async setCheckbox(label: string, checked: boolean): Promise<void> {
    const cb = this.page.getByLabel(label, { exact: false });
    if (checked) {
      await cb.check();
    } else {
      await cb.uncheck();
    }
  }

  async confirmModal(): Promise<void> {
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('networkidle');
  }

  async confirmDialogButton(confirmText: string = 'Delete'): Promise<void> {
    // Ibexa confirmation dialogs don't use the HTML `open` attribute — use JS to find the visible button
    await this.page.waitForFunction(() => {
      const allButtons = Array.from(document.querySelectorAll('button'));
      return allButtons.some(b => (b as HTMLElement).offsetParent !== null && b.textContent?.trim() === 'Cancel');
    }, { timeout: 5_000 }).catch(() => {});
    await this.page.evaluate((text) => {
      const allButtons = Array.from(document.querySelectorAll('button'));
      const cancelBtn = allButtons.find(b => (b as HTMLElement).offsetParent !== null && b.textContent?.trim() === 'Cancel');
      if (!cancelBtn) return;
      const container = cancelBtn.closest('dialog, [role="dialog"], .modal, .ibexa-modal');
      if (!container) return;
      const btn = Array.from(container.querySelectorAll('button')).find(b => b.textContent?.trim() === text);
      if (btn) (btn as HTMLElement).click();
    }, confirmText);
  }

  async switchToTab(tabName: string): Promise<void> {
    const tab = this.page.locator('.ibexa-tabs .nav-link, .nav-tabs .nav-link').filter({ hasText: tabName }).first();
    await tab.waitFor({ state: 'attached', timeout: 10_000 });
    await tab.dispatchEvent('click');
    await this.page.waitForTimeout(500);
  }

  async findTableRowAcrossPages(name: string): Promise<void> {
    const maxPages = 10;
    for (let p = 0; p < maxPages; p++) {
      // Wait for at least one row to be visible before checking for specific name
      await this.page.locator('.ibexa-table__row').first().waitFor({ state: 'visible', timeout: 5_000 }).catch(() => {});
      await this.page.waitForTimeout(300);
      const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
      if (await row.isVisible({ timeout: 3_000 }).catch(() => false)) return;
      // Ibexa uses pagerfanta with css_next_class='next' → <li class="page-item next"><a class="page-link">
      const nextPageBtn = this.page.locator('li.page-item.next:not(.disabled) a.page-link').first();
      if (!await nextPageBtn.isVisible({ timeout: 1_000 }).catch(() => false)) return;
      await nextPageBtn.click();
      await this.page.waitForLoadState('domcontentloaded');
      await this.page.waitForTimeout(500);
    }
  }

  async clickBulkDeleteButton(): Promise<void> {
    const btn = this.page.locator('button:not([disabled])').filter({ hasText: 'Delete' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click();
    await this.page.waitForLoadState('networkidle');
  }
}
