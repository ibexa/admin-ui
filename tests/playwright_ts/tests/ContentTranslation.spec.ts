import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '../lib/IbexaApiClient';
import { TranslationPage } from '../lib/TranslationPage';
import { ContentEditPage } from '../lib/ContentEditPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content item translation', () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test('Publish new translation based on existing translation', async ({ page }) => {
    const contentId = await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'EnglishPublished',
      short_name: 'EnglishPublished',
    });
    const locationId = await api.getMainLocationId(contentId);
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');

    const translator = new TranslationPage(page);
    await translator.switchToTranslationsTab();
    await translator.addTranslationBasedOn('Edited Deutsch', 'English (United Kingdom)');

    const editor = new ContentEditPage(page);
    await editor.fillTextField('name', 'GermanPublished');
    await editor.publish();

    await editor.assertSuccessNotification('Content published');

    // Check English attributes still present
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.ibexa-page-title h1, h1').first()).toContainText('EnglishPublished');
  });

  test('Publish new translation without base translation', async ({ page }) => {
    const contentId = await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'NoBasePublished',
      short_name: 'NoBasePublished',
    });
    const locationId = await api.getMainLocationId(contentId);
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');

    const translator = new TranslationPage(page);
    await translator.switchToTranslationsTab();
    await translator.addTranslationWithoutBase('Edited Deutsch');

    const editor = new ContentEditPage(page);
    await editor.fillTextField('name', 'GermanNoBase');
    await editor.publish();

    await editor.assertSuccessNotification('Content published');

    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.ibexa-page-title h1, h1').first()).toContainText('NoBasePublished');
  });
});
