import { test } from '@playwright/test';
import { MyDraftsPage } from '../lib/MyDraftsPage';
import { IbexaApiClient, EMPTY_RICHTEXT } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('My Drafts', () => {
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
  });

  test('It is possible to delete a draft', async ({ page }) => {
    await api.createDraft(
      await api.createContentItem('article', 2, 'eng-GB', { title: 'TestMyDraft', short_title: 'TestMyDraft', intro: EMPTY_RICHTEXT }),
      'eng-GB',
      { title: 'TestMyDraftDelta' },
    ).catch(() => null);

    // Create the draft via API
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title: 'TestMyDraftDelete',
      short_title: 'TestMyDraftDelete',
      intro: EMPTY_RICHTEXT,
    });
    await api.createDraft(contentId, 'eng-GB', { title: 'TestMyDraftDeleteDraft' });

    const drafts = new MyDraftsPage(page);
    await drafts.open(baseUrl);
    await drafts.assertDraftPresent('TestMyDraftDelete');
    await drafts.deleteDraft('TestMyDraftDelete');
    await drafts.assertDraftDeleted('TestMyDraftDelete');
  });

  test('It is possible to edit a draft', async ({ page }) => {
    const contentId = await api.createContentItem('article', 2, 'eng-GB', {
      title: 'TestMyDraftEdit',
      short_title: 'TestMyDraftEdit',
      intro: EMPTY_RICHTEXT,
    });
    await api.createDraft(contentId, 'eng-GB', { title: 'TestMyDraftEditDraft' });

    const drafts = new MyDraftsPage(page);
    await drafts.open(baseUrl);
    await drafts.assertDraftPresent('TestMyDraftEdit');
    await drafts.editDraft('TestMyDraftEdit');

    // Assert we are on the content edit page
    const { expect } = await import('@playwright/test');
    await expect(page).toHaveURL(/\/admin\/content\/edit/);
  });
});
