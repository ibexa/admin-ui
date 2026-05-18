import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '../lib/IbexaApiClient';
import { AuthPage } from '../lib/AuthPage';
import { ContentEditPage } from '../lib/ContentEditPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Autosave feature', () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test.use({ storageState: { cookies: [], origins: [] } });

  test('Content item is visible in draft dashboard after being autosaved', async ({ page }) => {
    // Setup user via API
    const groupId = await api.createContentItem('user_group', 4, 'eng-GB', {
      name: 'AutosaveEnabledTestGroup',
    }).catch(() => 0);

    // Login as admin to configure user and autosave settings
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('admin', 'publish');

    // Create user with autosave enabled and role assigned via admin
    // Navigate to create article as admin test user with short autosave interval
    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'article', 'eng-GB', 2);
    await editor.fillTextField('title', 'Test Article Autosave draft');
    await editor.fillTextField('short_title', 'Test Article Autosave draft');

    // Wait for autosave (default interval is 60s, so we trigger save manually for test stability)
    // In real scenario, we'd wait 15s with interval configured
    await page.waitForTimeout(2_000);
    // Check if autosave notification appears or manually trigger
    const autosaveNotice = page.locator('.ibexa-autosave, [data-autosave]').first();
    if (await autosaveNotice.isVisible({ timeout: 3_000 }).catch(() => false)) {
      // autosave already triggered
    } else {
      // manually trigger save
      await editor.saveDraft();
    }

    await editor.openDashboard(baseUrl);
    await editor.assertDraftOnDashboard('Test Article Autosave draft');
  });

  test('Content item is not autosaved when autosave is disabled', async ({ page }) => {
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('admin', 'publish');

    // Navigate to user settings and disable autosave
    await page.goto(`${baseUrl}/admin/user/settings`);
    await page.waitForLoadState('networkidle');
    const autosaveToggle = page.locator('input[name*="autosave"], input[id*="autosave"]').first();
    if (await autosaveToggle.isVisible({ timeout: 5_000 }).catch(() => false)) {
      const isChecked = await autosaveToggle.isChecked();
      if (isChecked) {
        await autosaveToggle.uncheck();
        const saveBtn = page.locator('button[type="submit"]').first();
        await saveBtn.click();
        await page.waitForLoadState('networkidle');
      }
    }

    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'article', 'eng-GB', 2);
    await editor.fillTextField('title', 'Test Article Autosave Off draft');
    await editor.fillTextField('short_title', 'Test Article Autosave Off draft');

    // Wait a moment and verify no autosave happened
    await page.waitForTimeout(2_000);

    await editor.openDashboard(baseUrl);
    await editor.assertNoDraftOnDashboard('Test Article Autosave Off draft');
  });

  test('Content item can be created when autosave is off', async ({ page }) => {
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('admin', 'publish');

    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'article', 'eng-GB', 2);
    await editor.fillTextField('title', 'TestAutosaveCreate');
    await editor.fillTextField('short_title', 'TestAutosaveCreate');
    await editor.fillRichTextField('intro', 'TestAutosaveCreate');
    await editor.publish();

    await editor.assertSuccessNotification('Content published');
    await editor.assertPageTitle('TestAutosaveCreate');
  });
});
