import { test } from '@playwright/test';
import { AuthPage } from '../lib/AuthPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Authentication', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('should be redirected to Dashboard after successful login', async ({ page }) => {
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('admin', 'publish');
    await auth.assertOnDashboard();
  });

  test('should be redirected to Login page from Dashboard when not logged in', async ({ page }) => {
    const auth = new AuthPage(page);
    await page.goto(`${baseUrl}/admin/dashboard`);
    await page.waitForLoadState('networkidle');
    await auth.assertOnLoginPage();
  });

  test('should be redirected to Login page after unsuccessful login', async ({ page }) => {
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('admin', 'notpublish');
    await auth.assertOnLoginPage();
  });

  test('should be redirected to the same page in back office after login', async ({ page }) => {
    const auth = new AuthPage(page);
    await page.goto(`${baseUrl}/admin/systeminfo`);
    await page.waitForLoadState('networkidle');
    await auth.assertOnLoginPage();
    await auth.login('admin', 'publish');
    await auth.assertOnPage('System Information');
  });
});
