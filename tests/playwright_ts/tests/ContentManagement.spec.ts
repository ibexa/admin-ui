import { test } from '@playwright/test';
import { ContentManagementPage } from '../lib/ContentManagementPage';
import { IbexaApiClient, EMPTY_RICHTEXT } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content management', () => {
  let api: IbexaApiClient;
  let parentContentId: number;
  let parentLocationId: number;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();

    parentContentId = await api.createFolder('ContentManagement', 2 /* content root location ID */);
    parentLocationId = await api.getMainLocationId(parentContentId);
  });

  test('content moving can be cancelled', async ({ page }) => {
    const contentId = await api.createFolder('FolderToCancelMove', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Move');
    await contentPage.selectInUDW('Media');
    await contentPage.closeUDW();

    await contentPage.assertOnContentView('FolderToCancelMove');
  });

  test('content can be moved', async ({ page }) => {
    const contentId = await api.createFolder('FolderToMove', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Move');
    await contentPage.selectInUDW('Media/Files');
    await contentPage.confirmUDW();

    await contentPage.assertSuccessNotification("'FolderToMove' moved to 'Files'");
    await contentPage.assertOnContentView('FolderToMove');

    await contentPage.open(baseUrl, parentContentId, parentLocationId);
    await contentPage.assertSubitemAbsent('FolderToMove');
  });

  test('content copying can be cancelled', async ({ page }) => {
    const contentId = await api.createFolder('FolderToCopyCancel', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Copy');
    await contentPage.selectInUDW('Media');
    await contentPage.closeUDW();

    await contentPage.assertOnContentView('FolderToCopyCancel');
  });

  test('content can be copied', async ({ page }) => {
    const contentId = await api.createFolder('FolderToCopy', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Copy');
    await contentPage.selectInUDW('Media/Files');
    await contentPage.confirmUDW();

    await contentPage.assertSuccessNotification("'FolderToCopy' copied to 'Files'");
    await contentPage.assertOnContentView('FolderToCopy');

    await contentPage.open(baseUrl, parentContentId, parentLocationId);
    await contentPage.assertSubitemPresent('FolderToCopy');
  });

  test('subtree copying can be cancelled', async ({ page }) => {
    const contentId = await api.createFolder('FolderToSubtreeCopyCancel', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Copy Subtree');
    await contentPage.selectInUDW('Media');
    await contentPage.closeUDW();

    await contentPage.assertOnContentView('FolderToSubtreeCopyCancel');
  });

  test('subtree can be copied', async ({ page }) => {
    const contentId = await api.createFolder('FolderToSubtreeCopy', parentLocationId);
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Copy Subtree');
    await contentPage.selectInUDW('Media');
    await contentPage.confirmUDW();

    await contentPage.assertSuccessNotification("Subtree 'FolderToSubtreeCopy' copied to Location 'Media'");
    await contentPage.assertOnContentView('FolderToSubtreeCopy');

    await contentPage.open(baseUrl, parentContentId, parentLocationId);
    await contentPage.assertSubitemPresent('FolderToSubtreeCopy');
  });

  test('content item can be hidden and revealed', async ({ page }) => {
    const contentId = await api.createContentItem('article', parentLocationId, 'eng-GB', {
      title: 'TestArticleToHide',
      short_title: 'TestArticleToHide',
      intro: EMPTY_RICHTEXT,
    });
    const locationId = await api.getMainLocationId(contentId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.hide();
    await contentPage.open(baseUrl, contentId, locationId);

    await contentPage.performAction('Reveal');
    await contentPage.assertSuccessNotification("Content item 'TestArticleToHide' revealed");
  });
});
