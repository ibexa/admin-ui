import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class SystemInfoPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async open(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/systeminfo`);
  }

  async goToTab(tabName: string): Promise<void> {
    await this.switchToTab(tabName);
    await this.page.waitForTimeout(1000);
  }

  async assertInfoTableVisible(section: string): Promise<void> {
    const tableOrDetails = this.page.locator(
      '.ibexa-tab-content__pane.active table, .ibexa-tab-content__pane.active .ibexa-details'
    ).first();
    await tableOrDetails.waitFor({ state: 'visible', timeout: 15_000 });
    await expect(tableOrDetails).toBeVisible();
  }

  async assertPackageListed(packageName: string): Promise<void> {
    const cell = this.page.locator('td').filter({ hasText: packageName }).first();
    await cell.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(cell).toContainText(packageName);
  }

  async assertBundleListed(bundleName: string): Promise<void> {
    const cell = this.page.locator('td').filter({ hasText: bundleName }).first();
    await cell.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(cell).toContainText(bundleName);
  }
}
