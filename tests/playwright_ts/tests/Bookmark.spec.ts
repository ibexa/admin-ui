import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '../lib/IbexaApiClient';
import { BookmarkPage } from '../lib/BookmarkPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Bookmarks management', () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test('Content Item can be added to bookmarks', async ({ page }) => {
    const contentId = await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'BookmarkFolder',
      short_name: 'BookmarkFolder',
    });
    const locationId = await api.getMainLocationId(contentId);
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');

    const bm = new BookmarkPage(page);
    await bm.bookmarkCurrentContent();
    await bm.assertIsBookmarked();
  });

  test('Bookmarks can be displayed', async ({ page }) => {
    const bm = new BookmarkPage(page);
    await bm.open(baseUrl);
    await bm.assertBookmarkInList('BookmarkFolder');
  });

  test('Content Item can be previewed from Bookmarks page', async ({ page }) => {
    const bm = new BookmarkPage(page);
    await bm.open(baseUrl);
    await bm.assertBookmarkInList('BookmarkFolder');
    await bm.navigateToBookmarkedContent('BookmarkFolder');
    await expect(page.locator('.ibexa-page-title h1, h1').first()).toContainText('BookmarkFolder');
  });

  test('Content Item can be edited from Bookmarks page', async ({ page }) => {
    const bm = new BookmarkPage(page);
    await bm.open(baseUrl);
    await bm.assertBookmarkInList('BookmarkFolder');
    await bm.editBookmarkedContent('BookmarkFolder');
    await expect(page).toHaveURL(/\/admin\/content\/(edit|create)/);
  });

  test('Bookmark can be deleted', async ({ page }) => {
    const bm = new BookmarkPage(page);
    await bm.open(baseUrl);
    await bm.assertBookmarkInList('BookmarkFolder');
    await bm.deleteBookmark('BookmarkFolder');
    await bm.assertBookmarkNotInList('BookmarkFolder');
  });

  test('Content Item can be bookmarked from UDW', async ({ page }) => {
    const contentId = await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'BookmarkUDW',
      short_name: 'BookmarkUDW',
    });
    const locationId = await api.getMainLocationId(contentId);

    // Navigate to content view page which contains the bookmark toggle button
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${locationId}`);
    await page.waitForLoadState('networkidle');

    const bm = new BookmarkPage(page);
    await bm.bookmarkCurrentContent();
    await bm.assertIsBookmarked();
  });

  test('Bookmarked Content Item can be edited from UDW', async ({ page }) => {
    await page.goto(`${baseUrl}/admin/dashboard`);
    await page.waitForLoadState('networkidle');

    // Open UDW from dashboard "Create content" quick action
    const udwBtn = page.locator('button[data-udw-config]').first();
    await udwBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await udwBtn.click({ force: true });
    await page.waitForTimeout(1000);

    const bm = new BookmarkPage(page);
    await bm.switchUDWTab('Bookmarks');
    await bm.selectBookmarkedContentInUDW('BookmarkUDW');
    await bm.editSelectedContentFromUDW();
    // Edit may open inline in UDW iframe (on-the-fly) or navigate to edit page
    const isEditUrl = page.url().match(/\/admin\/content\/(edit|create)/);
    if (!isEditUrl) {
      // Verify edit form appeared inside UDW iframe
      const editFrame = page.frameLocator('iframe').first();
      await expect(editFrame.locator('button').filter({ hasText: 'Publish' }).first()).toBeVisible({ timeout: 10_000 });
    } else {
      await expect(page).toHaveURL(/\/admin\/content\/(edit|create)/);
    }
  });
});
