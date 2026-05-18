import { Page, expect } from '@playwright/test';

export class AuthPage {
  constructor(private readonly page: Page) {}

  async openLogin(baseUrl: string): Promise<void> {
    await this.page.goto(`${baseUrl}/admin/login`);
    await this.page.waitForLoadState('domcontentloaded');
  }

  async login(username: string, password: string): Promise<void> {
    await this.page.locator('#username').waitFor({ state: 'visible', timeout: 30_000 });
    await this.page.locator('#username').pressSequentially(username);
    await this.page.keyboard.press('Tab');
    await this.page.locator('#password').pressSequentially(password);
    await this.page.keyboard.press('Tab');
    await this.page.locator('button[type="submit"].ibexa-login__btn--sign-in').waitFor({ state: 'visible', timeout: 10_000 });
    await this.page.locator('button[type="submit"].ibexa-login__btn--sign-in').click();
    await this.page.waitForLoadState('networkidle');
  }

  async assertOnDashboard(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin(\/dashboard|$|\?)/);
    await this.page.locator('.ibexa-main-container').waitFor({ state: 'visible', timeout: 10_000 });
  }

  async assertOnLoginPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/login/);
  }

  async assertOnPage(title: string): Promise<void> {
    const heading = this.page.locator('.ibexa-page-title h1');
    await heading.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(heading).toContainText(title, { ignoreCase: true });
  }
}
