import { test, expect } from '@playwright/test';
import { ContentEditPage } from '../lib/ContentEditPage';
import { IbexaApiClient, EMPTY_RICHTEXT } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content items creation', () => {
  let api: IbexaApiClient;
  const createdContentIds: number[] = [];
  const uid = Date.now().toString().slice(-6);

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test.afterAll(async () => {
    for (const id of createdContentIds) {
      await api.deleteContent(id).catch(() => {});
    }
  });

  test('Content draft can be saved', async ({ page }) => {
    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'article', 'eng-GB', 2);
    await editor.fillTextField('title', 'Test Article draft');
    await editor.fillTextField('short_title', 'Test Article draft');
    await editor.saveDraft();

    await editor.assertSuccessNotification('Content draft saved');

    await editor.openDashboard(baseUrl);
    await editor.assertDraftOnDashboard('Test Article draft');
  });

  test('Content draft can be deleted', async ({ page }) => {
    const title = `TDraft_${uid}`;
    const draftTitle = `TDraftV2_${uid}`;
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title,
      short_title: title,
      intro: EMPTY_RICHTEXT,
    });
    createdContentIds.push(contentId);
    await api.createDraft(contentId, 'eng-GB', { title: draftTitle });

    const editor = new ContentEditPage(page);
    await editor.openDashboard(baseUrl);
    await editor.assertDraftOnDashboard(title);
    await editor.openDraftForEditing(title);
    await editor.deleteDraft();

    await editor.openDashboard(baseUrl);
    await editor.assertNoDraftOnDashboard(draftTitle);
  });

  test('Content draft can be saved and then published', async ({ page }) => {
    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'article', 'eng-GB', 2);
    await editor.fillTextField('title', 'TestArticleSavePublish');
    await editor.fillTextField('short_title', 'TestArticleSavePublish');
    await editor.fillRichTextField('intro', 'TestArticleIntro');
    await editor.saveDraftAndClose();

    // saveDraftAndClose redirects to parent content view — reopen draft from dashboard
    await editor.openDashboard(baseUrl);
    await editor.openDraftForEditing('TestArticleSavePublish');

    await editor.publish();
    await editor.assertSuccessNotification('Content published');
    await editor.assertPageTitle('TestArticleSavePublish');
  });

  test('Content draft edition can be closed', async ({ page }) => {
    const title = `TestDraftEdit_${uid}`;
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title,
      short_title: title,
      intro: EMPTY_RICHTEXT,
    });
    createdContentIds.push(contentId);
    await api.createDraft(contentId, 'eng-GB', { title: `${title}Draft` });

    const editor = new ContentEditPage(page);
    await editor.openDashboard(baseUrl);
    await editor.assertDraftOnDashboard(title);
    await editor.openDraftForEditing(title);
    await expect(page).toHaveURL(/\/admin\/content\/(edit|create)/);

    await editor.deleteDraft();
    // After deleting draft from edit, should navigate to content view or root
    await page.waitForLoadState('networkidle');
    await expect(page).not.toHaveURL(/\/admin\/content\/(edit|create)/);
  });

  test('Content draft can be created and published through draft list modal', async ({ page }) => {
    const title = `DraftConflict_${uid}`;
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title,
      short_title: title,
      intro: EMPTY_RICHTEXT,
    });
    createdContentIds.push(contentId);
    await api.createDraft(contentId, 'eng-GB', { title: `${title}V1` });

    const contentLocId = await api.getMainLocationId(contentId);
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${contentLocId}`);
    await page.waitForLoadState('networkidle');

    // Click Edit — conflict modal should appear
    const editBtn = page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Edit' }).first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.dispatchEvent('click');
    await page.waitForLoadState('networkidle');

    const editor = new ContentEditPage(page);
    await editor.startCreatingNewDraftFromConflictModal();
    await editor.fillTextField('title', `${title}V2`);
    await editor.fillTextField('short_title', `${title}V2`);
    await editor.fillRichTextField('intro', 'DraftConflict intro text');
    await editor.publish();

    await editor.assertSuccessNotification('Content published');
    await editor.assertPageTitle(`${title}V2`);
  });

  test('Content draft from draft list modal can be published', async ({ page }) => {
    const title = `DraftModalPublish_${uid}`;
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title,
      short_title: title,
      intro: EMPTY_RICHTEXT,
    });
    createdContentIds.push(contentId);
    await api.createDraft(contentId, 'eng-GB', { title: `${title}V2` });

    const contentLocId = await api.getMainLocationId(contentId);
    await page.goto(`${baseUrl}/admin/view/content/${contentId}/full/1/${contentLocId}`);
    await page.waitForLoadState('networkidle');

    const editBtn = page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Edit' }).first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.dispatchEvent('click');
    await page.waitForLoadState('networkidle');

    const editor = new ContentEditPage(page);
    await editor.editDraftWithVersionFromConflictModal(2);
    await editor.fillRichTextField('intro', 'ContentDraftConflictFromTheListVersion2Edited');
    await editor.publish();

    await editor.assertSuccessNotification('Content published');
    await editor.assertPageTitle(title);
  });
});
