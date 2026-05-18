import { test } from '@playwright/test';
import { ContentTypeGroupPage } from '../lib/ContentTypeGroupPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content type groups management', () => {
  let ctg: ContentTypeGroupPage;

  test.beforeAll(async () => {
    const api = new IbexaApiClient(baseUrl);
    await api.init();
    await api.deleteContentTypeGroupByName('Test content type Group');
    await api.deleteContentTypeGroupByName('Test content type Group edited');
  });

  test.beforeEach(async ({ page }) => {
    ctg = new ContentTypeGroupPage(page);
    await ctg.openList(baseUrl);
  });

  test('Changes can be discarded while creating new content type group', async () => {
    await ctg.clickCreate();
    await ctg.fillForm('Test content type Group');
    await ctg.discard();
    await ctg.assertOnListPage();
    await ctg.assertGroupNotInList('Test content type Group');
  });

  test('New content type group can be added', async () => {
    await ctg.clickCreate();
    await ctg.fillForm('Test content type Group');
    await ctg.save();
    await ctg.assertOnGroupPage('Test content type Group');
    await ctg.assertGroupHasNoContentTypes();
  });

  test('Changes can be discarded while editing content type group', async () => {
    await ctg.assertGroupInList('Test content type Group');
    await ctg.editFromList('Test content type Group');
    await ctg.fillForm('Test content type Group edited');
    await ctg.discardChanges();
    await ctg.assertOnListPage();
    await ctg.assertGroupInList('Test content type Group');
    await ctg.assertGroupNotInList('Test content type Group edited');
  });

  test('Content type group can be edited', async () => {
    await ctg.assertGroupInList('Test content type Group');
    await ctg.editFromList('Test content type Group');
    await ctg.fillForm('Test content type Group edited');
    await ctg.save();
    await ctg.assertOnGroupPage('Test content type Group edited');
    await ctg.assertSuccessNotification("Updated content type group 'Test content type Group'");
  });

  test('Content type group can be deleted', async () => {
    await ctg.assertGroupInList('Test content type Group edited');
    await ctg.deleteFromList('Test content type Group edited');
    await ctg.assertSuccessNotification("Deleted content type group 'Test content type Group edited'");
    await ctg.assertGroupNotInList('Test content type Group edited');
  });

  test('Non-empty content type group cannot be deleted', async () => {
    const row = ctg['page'].locator('.ibexa-table__row').filter({ hasText: 'Content' }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const checkbox = row.locator('input[type="checkbox"]').first();
    const { expect } = await import('@playwright/test');
    await expect(checkbox).toBeDisabled();
  });
});
