import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class SearchPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async searchGlobal(query: string): Promise<void> {
    const input = this.page.locator('#ibexa-global-search-form input, .ibexa-global-search__input').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(query);
    await input.press('Enter');
    await this.page.waitForLoadState('networkidle');
  }

  async assertSearchResultContains(name: string): Promise<void> {
    const result = this.page.locator('.ibexa-table__row, .ibexa-search-results__item').filter({ hasText: name }).first();
    await result.waitFor({ state: 'visible', timeout: 15_000 });
    await expect(result).toBeVisible();
  }

  async openUDWFromDashboard(): Promise<void> {
    const udwBtn = this.page.locator('button[data-udw-config]').first();
    await udwBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await udwBtn.click({ force: true });
    await this.page.waitForTimeout(1000);
  }

  async openSearchInUDW(): Promise<void> {
    // UDW tab selector renders icon-only tabs with title attribute (c-tab-selector__item)
    const searchTab = this.page.locator('.m-ud .c-tab-selector__item[title="Search"], .m-ud .c-tab-selector__item[title*="Search"]').first();
    await searchTab.waitFor({ state: 'visible', timeout: 10_000 });
    await searchTab.click({ force: true });
    await this.page.waitForTimeout(500);
  }

  async searchInUDW(query: string): Promise<void> {
    const input = this.page.locator('.m-ud input[type="search"], .m-ud .ibexa-global-search__input, .m-ud input[name*="search"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(query);
    await input.press('Enter');
    await this.page.waitForTimeout(1_000);
  }

  async selectSearchResultInUDW(name: string): Promise<void> {
    const item = this.page.locator('.m-ud .ibexa-table__row, .m-ud .c-finder-leaf').filter({ hasText: name }).first();
    await item.waitFor({ state: 'visible', timeout: 10_000 });
    await item.locator('a, .c-finder-leaf__name').first().click();
    await this.page.waitForTimeout(500);
  }

  async editSelectedInUDW(): Promise<void> {
    const editBtn = this.page.locator('.m-ud').locator('button, a').filter({ hasText: 'Edit' }).last();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.click();
    await this.page.waitForLoadState('networkidle');
  }
}
