import { test, expect } from '@playwright/test';
import { ContentManagementPage } from '../lib/ContentManagementPage';
import { IbexaApiClient, EMPTY_RICHTEXT } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content tree basic operations', () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test('Content tree can be displayed', async ({ page }) => {
    const contentPage = new ContentManagementPage(page);
    // Navigate to content root
    await page.goto(`${baseUrl}/admin/view/content/52/full/1/2`);
    await page.waitForLoadState('networkidle');

    const contentTree = page.locator('.ibexa-content-tree, .c-content-tree, [class*="content-tree"]').first();
    await contentTree.waitFor({ state: 'visible', timeout: 15_000 });
    await expect(contentTree).toBeVisible();
  });

  test('It is possible to display items on Content tree', async ({ page }) => {
    const art1Id = await api.createContentItem('article', 2, 'eng-GB', {
      title: 'TreeArticle1',
      short_title: 'TreeArticle1',
      intro: EMPTY_RICHTEXT,
    });
    const art1LocId = await api.getMainLocationId(art1Id);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, art1Id, art1LocId);

    const contentTree = page.locator('.ibexa-content-tree, .c-content-tree, [class*="content-tree"]').first();
    await contentTree.waitFor({ state: 'visible', timeout: 15_000 });
    const treeItem = contentTree.locator('*').filter({ hasText: 'TreeArticle1' }).first();
    await expect(treeItem).toBeVisible({ timeout: 15_000 });
  });

  test('New Content item can be created under chosen nested node', async ({ page }) => {
    const art1Id = await api.createContentItem('article', 2, 'eng-GB', {
      title: 'TreeArticle2',
      short_title: 'TreeArticle2',
      intro: EMPTY_RICHTEXT,
    });
    const art1LocId = await api.getMainLocationId(art1Id);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, art1Id, art1LocId);

    const contentTree = page.locator('.ibexa-content-tree, .c-content-tree, [class*="content-tree"]').first();
    await contentTree.waitFor({ state: 'visible', timeout: 15_000 });
    await expect(contentTree).toBeVisible();
    await expect(page.locator('.ibexa-page-title h1')).toContainText('TreeArticle2');
  });
});
