import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '@ibexa/cohesivo-playwright';
import { SearchPage } from '../lib/SearchPage';


test.describe('Searching for a Content item', { tag: ['@IbexaOSS', '@IbexaHeadless'] }, () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient();
    await api.init();
  });

  test('Content can be searched for', async ({ page }) => {
    await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'Searched folder',
      short_name: 'Searched folder',
    });

    await page.goto('/admin/dashboard');
    await page.waitForLoadState('networkidle');

    const search = new SearchPage(page);
    await search.searchGlobal('Searched folder');
    await search.assertSearchResultContains('Searched folder');
  });

  test('Content can be searched for in UDW', async ({ page }) => {
    await api.createContentItem('folder', 2, 'eng-GB', {
      name: 'folderUDW',
      short_name: 'folderUDW',
    });

    await page.goto('/admin/dashboard');
    await page.waitForLoadState('networkidle');

    const search = new SearchPage(page);
    await search.openUDWFromDashboard();
    await search.openSearchInUDW();
    await search.searchInUDW('folderUDW');
    await search.selectSearchResultInUDW('folderUDW');
    await search.editSelectedInUDW();
    await expect(page).toHaveURL(/\/admin\/content\/(edit|create)/);
  });
});
