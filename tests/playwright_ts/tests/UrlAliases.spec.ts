import { test, expect } from '@playwright/test';
import { ContentManagementPage } from '../lib/ContentManagementPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('URL Aliases', () => {
  let api: IbexaApiClient;
  let contentId: number;
  let locationId: number;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();

    contentId = await api.createFolder('UrlAliases', 2);
    locationId = await api.getMainLocationId(contentId);
  });

  test('Create a redirect Url Alias', async ({ page }) => {
    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    // Switch to URL tab
    const urlTab = page.locator('.ibexa-tabs .nav-link, .nav-tabs .nav-link').filter({ hasText: 'URL' }).first();
    await urlTab.waitFor({ state: 'attached', timeout: 10_000 });
    await urlTab.dispatchEvent('click');
    await page.waitForTimeout(500);

    // Click "Add" button in URL aliases section
    const addBtn = page.getByRole('tabpanel', { name: 'URL' }).getByRole('button', { name: 'Add' }).first();
    await addBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await addBtn.click();

    // Wait for "Create a new URL alias" dialog
    const dialog = page.locator('dialog[open], [role="dialog"]').filter({ hasText: 'Create a new URL alias' }).first();
    await dialog.waitFor({ state: 'visible', timeout: 10_000 });

    // Fill URL field
    const urlInput = dialog.locator('input[type="text"], input:not([type])').first();
    await urlInput.fill('RedirectUrlAlias');

    // Ensure redirect checkbox is checked (it's on by default)
    const redirectCheckbox = dialog.locator('input[type="checkbox"]').first();
    if (await redirectCheckbox.isVisible() && !await redirectCheckbox.isChecked()) {
      await page.evaluate((el) => (el as HTMLInputElement).click(), await redirectCheckbox.elementHandle());
    }

    // Click "Create" to save
    await dialog.getByRole('button', { name: 'Create' }).click();
    await page.waitForLoadState('networkidle');

    // Assert alias in list
    const aliasList = page.locator('.ibexa-table__row').filter({ hasText: 'RedirectUrlAlias' }).first();
    await aliasList.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(aliasList).toBeVisible();
  });

  test('Create a direct Url Alias', async ({ page }) => {
    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    const urlTab = page.locator('.ibexa-tabs .nav-link, .nav-tabs .nav-link').filter({ hasText: 'URL' }).first();
    await urlTab.waitFor({ state: 'attached', timeout: 10_000 });
    await urlTab.dispatchEvent('click');
    await page.waitForTimeout(500);

    const addBtn = page.getByRole('tabpanel', { name: 'URL' }).getByRole('button', { name: 'Add' }).first();
    await addBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await addBtn.click();

    const dialog = page.locator('dialog[open], [role="dialog"]').filter({ hasText: 'Create a new URL alias' }).first();
    await dialog.waitFor({ state: 'visible', timeout: 10_000 });

    const urlInput = dialog.locator('input[type="text"], input:not([type])').first();
    await urlInput.fill('DirectUrlAlias');

    // Ensure redirect checkbox is NOT checked (uncheck it if on)
    const redirectCheckbox = dialog.locator('input[type="checkbox"]').first();
    if (await redirectCheckbox.isVisible() && await redirectCheckbox.isChecked()) {
      await page.evaluate((el) => (el as HTMLInputElement).click(), await redirectCheckbox.elementHandle());
    }

    await dialog.getByRole('button', { name: 'Create' }).click();
    await page.waitForLoadState('networkidle');

    const aliasList = page.locator('.ibexa-table__row').filter({ hasText: 'DirectUrlAlias' }).first();
    await aliasList.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(aliasList).toBeVisible();
  });
});
