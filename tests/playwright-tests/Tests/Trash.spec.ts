import { test, expect } from '@playwright/test';
import { TrashPage, ContentManagementPage, IbexaApiClient } from '@ibexa/cohesivo-playwright';


test.describe('Trash management', { tag: ['@IbexaOSS', '@IbexaHeadless', '@IbexaExperience', '@IbexaCommerce'] }, () => {
    let api: IbexaApiClient;
    let trashTestLocationId: number;
    let trashTestContentId: number;
    let runId: string;

    test.beforeAll(async () => {
        api = new IbexaApiClient();
        await api.init();
        runId = Date.now().toString().slice(-6);

        trashTestContentId = await api.createFolder(`TrashTest${runId}`, 2);
        trashTestLocationId = await api.getMainLocationId(trashTestContentId);
    });

    test.afterAll(async () => {
        if (api && trashTestContentId) {
            await api.deleteContent(trashTestContentId);
        }
    });

    test('Trash can be emptied', async ({ page }) => {
        const childId = await api.createFolder(`FolderToTrash${runId}`, trashTestLocationId);
        const childLocId = await api.getMainLocationId(childId);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId, childLocId);
        await contentPage.sendToTrash();

        const trash = new TrashPage(page);
        await trash.open();
        await trash.assertNotEmpty();
        await trash.emptyTrash();
        await trash.assertEmpty();
    });

    test('Content can be moved to trash', async ({ page }) => {
        const name = `FolderToTrashManually${runId}`;
        const childId = await api.createFolder(name, trashTestLocationId);
        const childLocId = await api.getMainLocationId(childId);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId, childLocId);
        await contentPage.sendToTrash();

        await contentPage.notifications.assertSuccess(`Location '${name}' moved to Trash`);

        const trash = new TrashPage(page);
        await trash.open();
        await trash.assertItemInTrash(name);
    });

    test('Element in trash can be deleted', async ({ page }) => {
        const name = `DeleteFromTrash${runId}`;
        const childId = await api.createFolder(name, trashTestLocationId);
        const childLocId = await api.getMainLocationId(childId);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId, childLocId);
        await contentPage.sendToTrash();

        const trash = new TrashPage(page);
        await trash.open();
        await trash.assertItemInTrash(name);
        await trash.deleteFromTrash([name]);
        await trash.notifications.assertSuccess('Deleted selected item(s) from Trash');
        await trash.assertItemNotInTrash(name);
    });

    test('Element in trash can be restored', async ({ page }) => {
        const name = `RestoreFromTrash${runId}`;
        const childId = await api.createFolder(name, trashTestLocationId);
        const childLocId = await api.getMainLocationId(childId);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId, childLocId);
        await contentPage.sendToTrash();

        const trash = new TrashPage(page);
        await trash.open();
        await trash.assertItemInTrash(name);
        await trash.restoreFromTrash([name]);
        await trash.notifications.assertSuccess('Restored content to its original Location');
        await trash.assertItemNotInTrash(name);
    });

    test('Element in trash can be restored under new location', async ({ page }) => {
        const name = `RestoreFromTrashNewLocation${runId}`;
        const childId = await api.createFolder(name, trashTestLocationId);
        const childLocId = await api.getMainLocationId(childId);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId, childLocId);
        await contentPage.sendToTrash();

        const trash = new TrashPage(page);
        await trash.open();
        await trash.assertItemInTrash(name);
        await trash.restoreUnderNewLocation([name], 'Media/Files');

        await trash.notifications.assertSuccess("Restored content under Location 'Files'");
        await trash.assertItemNotInTrash(name);

        // Verify the content actually landed under Media/Files (path resolution throws if absent)
        const restoredContentId = await api.getContentIdByPath(`Media/Files/${name}`);
        expect(restoredContentId).toBe(childId);

        await api.deleteContent(childId);
    });

    test('Element in trash can be found by search', async ({ page }) => {
        const name1 = `TrashSearch1${runId}`;
        const name2 = `TrashSearch2${runId}`;
        const childId1 = await api.createFolder(name1, trashTestLocationId);
        const childLocId1 = await api.getMainLocationId(childId1);
        const childId2 = await api.createFolder(name2, trashTestLocationId);
        const childLocId2 = await api.getMainLocationId(childId2);

        const contentPage = new ContentManagementPage(page);
        await contentPage.open(childId1, childLocId1);
        await contentPage.sendToTrash();
        await contentPage.open(childId2, childLocId2);
        await contentPage.sendToTrash();

        const trash = new TrashPage(page);
        await trash.open();
        await trash.searchInTrash(name1);
        await trash.assertItemInTrash(name1);
        await trash.assertItemNotInTrash(name2);
    });
});
