import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class UserProfilePage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async goToUserProfile(): Promise<void> {
    const profileLink = this.page.locator('.ibexa-user-menu__profile-link, a[href*="/user/profile"]').first()
      .or(this.page.locator('.ibexa-header-user-menu').locator('a').filter({ hasText: /profile/i }).first());
    await profileLink.waitFor({ state: 'visible', timeout: 10_000 });
    await profileLink.click();
    await this.page.waitForLoadState('networkidle');
  }

  async assertOnUserProfilePage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/user\/profile/);
  }

  async editProfileSummary(): Promise<void> {
    const editBtn = this.page.locator('button, a').filter({ hasText: 'Edit' }).first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  async switchToFieldGroup(groupName: string): Promise<void> {
    const tab = this.page.locator('.ibexa-tabs .nav-link, [role="tab"]').filter({ hasText: groupName }).first();
    await tab.waitFor({ state: 'attached', timeout: 10_000 });
    await tab.dispatchEvent('click');
    await this.page.waitForTimeout(300);
  }

  async fillProfileField(fieldId: string, value: string): Promise<void> {
    const input = this.page.locator(`input[id*="${fieldId}"], input[name*="${fieldId}"]`).first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async assertProfileSummaryValues(values: {
    fullName?: string;
    email?: string;
    jobTitle?: string;
    department?: string;
    location?: string;
  }): Promise<void> {
    for (const [, val] of Object.entries(values)) {
      if (val) {
        const el = this.page.getByText(val).first();
        await el.waitFor({ state: 'visible', timeout: 10_000 });
        await expect(el).toBeVisible();
      }
    }
  }
}
