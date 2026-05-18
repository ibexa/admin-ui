import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '../lib/IbexaApiClient';
import { UserPage } from '../lib/UserPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('User management', () => {
  let api: IbexaApiClient;
  let usersLocationId: number;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
    usersLocationId = await api.getContentIdByPath('/Users').then(
      async (id) => api.getMainLocationId(id)
    ).catch(() => 5); // fallback location id for Users
  });

  test('Create a new user', async ({ page }) => {
    const userPage = new UserPage(page);
    await userPage.startCreatingUser(page, baseUrl, usersLocationId);
    await userPage.fillFirstName('testuser');
    await userPage.fillLastName('lastname');
    await userPage.fillUsername('testuser');
    await userPage.fillPassword('Test1234pw');
    await userPage.fillConfirmPassword('Test1234pw');
    await userPage.fillEmail('test@test.com');
    await userPage.setEnabled(true);
    await userPage.create();

    await userPage.assertOnContentViewFor('Users/testuser lastname');
    await userPage.assertContentAttributes([
      { label: 'First name', value: 'testuser' },
      { label: 'Last name', value: 'lastname' },
      { label: 'Username', value: 'testuser' },
      { label: 'Email', value: 'test@test.com' },
    ]);
  });

  test('Editing an existing user', async ({ page }) => {
    const userId = await api.getContentIdByPath('/Users/testuser lastname').catch(() => 0);
    if (userId) {
      const locationId = await api.getMainLocationId(userId);
      await page.goto(`${baseUrl}/admin/view/content/${userId}/full/1/${locationId}`);
      await page.waitForLoadState('networkidle');
    } else {
      await page.goto(`${baseUrl}/admin/dashboard`);
    }

    const userPage = new UserPage(page);
    await userPage.performEditAction();
    await userPage.fillFirstName('testuseredited');
    await userPage.fillLastName('lastnameedited');
    await userPage.fillPassword('Test123456');
    await userPage.fillConfirmPassword('Test123456');
    await userPage.fillEmail('test@test.org');
    await userPage.update();

    await userPage.assertOnContentViewFor('Users/testuseredited lastnameedited');
    await userPage.assertContentAttributes([
      { label: 'First name', value: 'testuseredited' },
      { label: 'Last name', value: 'lastnameedited' },
      { label: 'Email', value: 'test@test.org' },
    ]);
  });
});
