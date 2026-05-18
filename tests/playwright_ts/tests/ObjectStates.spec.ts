import { test } from '@playwright/test';
import { ObjectStatesPage } from '../lib/ObjectStatesPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Object States management', () => {
  let page_: ObjectStatesPage;

  test.beforeAll(async () => {
    const api = new IbexaApiClient(baseUrl);
    await api.init();
    await api.deleteObjectStateGroupByName('Test Object State Group');
    await api.deleteObjectStateGroupByName('Test Object State Group edited');
    await api.deleteObjectStateGroupByName('Test Object State Group edited2');
  });

  test.beforeEach(async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
  });

  test('Changes can be discarded while creating Object state group', async () => {
    await page_.clickCreateGroup();
    await page_.fillGroupForm('Test Object State Group', 'TestObjectStateGroupIdentifier');
    await page_.discard();
    await page_.assertOnGroupListPage();
    await page_.assertGroupNotInList('Test Object State Group');
  });

  test('New Object state group can be added', async () => {
    await page_.clickCreateGroup();
    await page_.fillGroupForm('Test Object State Group', 'TestObjectStateGroupIdentifier');
    await page_.save();
    await page_.assertOnGroupPage('Test Object State Group');
    await page_.assertGroupHasNoStates();
    await page_.assertGroupAttributes('Test Object State Group', 'TestObjectStateGroupIdentifier');
    await page_.assertSuccessNotification('Test Object State Group');
  });

  test('Changes can be discarded while editing Object state groups', async () => {
    await page_.assertGroupInList('Test Object State Group');
    await page_.editGroupFromList('Test Object State Group');
    await page_.fillGroupForm('Test Object State Group edited');
    await page_.discardChanges();
    await page_.assertOnGroupListPage();
    await page_.assertGroupInList('Test Object State Group');
    await page_.assertGroupNotInList('Test Object State Group edited');
  });

  test('Object state group can be edited', async () => {
    await page_.assertGroupInList('Test Object State Group');
    await page_.editGroupFromList('Test Object State Group');
    await page_.fillGroupForm('Test Object State Group edited');
    await page_.save();
    await page_.assertSuccessNotification('Test Object State Group edited');
    await page_.assertOnGroupPage('Test Object State Group edited');
  });

  test('Changes can be discarded while editing Object state group from group details', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited');

    await page_.startEditingFromDetailPage();
    await page_.fillGroupForm('Test Object State Group edited2');
    await page_.discardChanges();
    await page_.assertOnGroupListPage();
    await page_.assertGroupInList('Test Object State Group edited');
    await page_.assertGroupNotInList('Test Object State Group edited2');
  });

  test('Object state group can be edited from group details', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited');

    await page_.startEditingFromDetailPage();
    await page_.fillGroupForm('Test Object State Group edited2');
    await page_.save();
    await page_.assertSuccessNotification('Test Object State Group edited2');
    await page_.assertOnGroupPage('Test Object State Group edited2');
  });

  test('Object state creation can be discarded', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.clickCreateState();
    await page_.fillStateForm('Test Object State', 'TestObjectStateIdentifier');
    await page_.discardChanges();
    await page_.assertOnGroupPage('Test Object State Group edited2');
    await page_.assertStateNotInGroup('Test Object State');
  });

  test('New Object state can be added', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.clickCreateState();
    await page_.fillStateForm('Test Object State', 'TestObjectStateIdentifier');
    await page_.save();
    await page_.assertOnStatePage('Test Object State');
    await page_.assertStateAttributes('Test Object State', 'TestObjectStateIdentifier');
    await page_.assertSuccessNotification('Test Object State');
  });

  test('Changes can be discarded while editing Object state', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.editStateFromGroupPage('Test Object State');
    await page_.fillStateForm('Test Object State edited');
    await page_.discardChanges();
    await page_.assertOnGroupPage('Test Object State Group edited2');
    await page_.assertStateInGroup('Test Object State');
    await page_.assertStateNotInGroup('Test Object State edited');
  });

  test('Object state can be edited', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.editStateFromGroupPage('Test Object State');
    await page_.fillStateForm('Test Object State edited');
    await page_.save();
    await page_.assertSuccessNotification('Test Object State edited');
    await page_.assertOnStatePage('Test Object State edited');
    await page_.assertStateAttributes('Test Object State edited', 'TestObjectStateIdentifier');
  });

  test('Changes can be discarded while editing Object state from state details', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');
    await page_.openState('Test Object State edited');

    await page_.startEditingFromDetailPage();
    await page_.fillStateForm('Test Object State edited2');
    await page_.discardChanges();
    await page_.assertOnGroupPage('Test Object State Group edited2');
    await page_.assertStateInGroup('Test Object State edited');
    await page_.assertStateNotInGroup('Test Object State edited2');
  });

  test('Object State can be edited from state details', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');
    await page_.openState('Test Object State edited');

    await page_.startEditingFromDetailPage();
    await page_.fillStateForm('Test Object State edited2');
    await page_.save();
    await page_.assertSuccessNotification('Test Object State edited2');
    await page_.assertOnStatePage('Test Object State edited2');
    await page_.assertStateAttributes('Test Object State edited2', 'TestObjectStateIdentifier');
  });

  test('Second object state can be created', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.clickCreateState();
    await page_.fillStateForm('Test Object State 2', 'TestObjectStateIdentifier2');
    await page_.save();
    await page_.assertOnStatePage('Test Object State 2');
    await page_.assertStateAttributes('Test Object State 2', 'TestObjectStateIdentifier2');
    await page_.assertSuccessNotification('Test Object State 2');
  });

  test('Object state can be deleted', async ({ page }) => {
    page_ = new ObjectStatesPage(page);
    await page_.openGroupsList(baseUrl);
    await page_.openGroup('Test Object State Group edited2');

    await page_.deleteState('Test Object State 2');
    await page_.assertSuccessNotification('Test Object State 2');
    await page_.assertStateNotInGroup('Test Object State 2');
  });

  test('Object State group can be deleted', async () => {
    await page_.assertGroupInList('Test Object State Group edited2');
    await page_.deleteGroup('Test Object State Group edited2');
    await page_.assertSuccessNotification('Test Object State Group edited2');
    await page_.assertGroupNotInList('Test Object State Group edited2');
  });
});
