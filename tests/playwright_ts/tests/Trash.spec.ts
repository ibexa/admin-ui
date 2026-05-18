import { test, expect } from '@playwright/test';
import { TrashPage } from '../lib/TrashPage';
import { ContentManagementPage } from '../lib/ContentManagementPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Trash management', () => {
  let api: IbexaApiClient;
  let trashTestLocationId: number;
  let trashTestContentId: number;
  let runId: string;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
    runId = Date.now().toString().slice(-6);

    trashTestContentId = await api.createFolder('TrashTest', 2);
    trashTestLocationId = await api.getMainLocationId(trashTestContentId);
  });

  test('Trash can be emptied', async ({ page }) => {
    const childId = await api.createFolder(`FolderToTrash${runId}`, trashTestLocationId);
    const childLocId = await api.getMainLocationId(childId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId, childLocId);
    await contentPage.sendToTrash();

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.assertNotEmpty();
    await trash.emptyTrash();
    await trash.assertEmpty();
  });

  test('Content can be moved to trash', async ({ page }) => {
    const name = `FolderToTrashManually${runId}`;
    const childId = await api.createFolder(name, trashTestLocationId);
    const childLocId = await api.getMainLocationId(childId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId, childLocId);
    await contentPage.sendToTrash();

    await contentPage.assertSuccessNotification(`Location '${name}' moved to Trash`);

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.assertItemInTrash(name);
  });

  test('Element in trash can be deleted', async ({ page }) => {
    const name = `DeleteFromTrash${runId}`;
    const childId = await api.createFolder(name, trashTestLocationId);
    const childLocId = await api.getMainLocationId(childId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId, childLocId);
    await contentPage.sendToTrash();

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.assertItemInTrash(name);
    await trash.deleteFromTrash([name]);
    await trash.assertSuccessNotification('Deleted selected item(s) from Trash');
    await trash.assertItemNotInTrash(name);
  });

  test('Element in trash can be restored', async ({ page }) => {
    const name = `RestoreFromTrash${runId}`;
    const childId = await api.createFolder(name, trashTestLocationId);
    const childLocId = await api.getMainLocationId(childId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId, childLocId);
    await contentPage.sendToTrash();

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.assertItemInTrash(name);
    await trash.restoreFromTrash([name]);
    await trash.assertSuccessNotification('Restored content to its original Location');
    await trash.assertItemNotInTrash(name);
  });

  test('Element in trash can be restored under new location', async ({ page }) => {
    const name = `RestoreFromTrashNewLocation${runId}`;
    const childId = await api.createFolder(name, trashTestLocationId);
    const childLocId = await api.getMainLocationId(childId);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId, childLocId);
    await contentPage.sendToTrash();

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.assertItemInTrash(name);
    await trash.restoreUnderNewLocation([name], 'Media/Files');

    const contentMgmt = new ContentManagementPage(page);
    await contentMgmt.selectInUDW('Media/Files');
    await contentMgmt.confirmUDW();

    await trash.assertSuccessNotification("Restored content under Location 'Files'");
    await trash.assertItemNotInTrash(name);
  });

  test('Element in trash can be found by search', async ({ page }) => {
    const name1 = `TrashSearch1${runId}`;
    const name2 = `TrashSearch2${runId}`;
    const childId1 = await api.createFolder(name1, trashTestLocationId);
    const childLocId1 = await api.getMainLocationId(childId1);
    const childId2 = await api.createFolder(name2, trashTestLocationId);
    const childLocId2 = await api.getMainLocationId(childId2);

    const contentPage = new ContentManagementPage(page);
    await contentPage.open(baseUrl, childId1, childLocId1);
    await contentPage.sendToTrash();
    await contentPage.open(baseUrl, childId2, childLocId2);
    await contentPage.sendToTrash();

    const trash = new TrashPage(page);
    await trash.open(baseUrl);
    await trash.searchInTrash(name1);
    await trash.assertItemInTrash(name1);
  });
});
