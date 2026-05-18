import { test, expect } from '@playwright/test';
import { IbexaApiClient } from '../lib/IbexaApiClient';
import { UserPage } from '../lib/UserPage';
import { UserProfilePage } from '../lib/UserProfilePage';
import { AuthPage } from '../lib/AuthPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('User profile management', () => {
  let api: IbexaApiClient;
  let editorsLocationId: number;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
    editorsLocationId = await api.getContentIdByPath('/Users/Editors').then(
      async (id) => api.getMainLocationId(id)
    ).catch(() => 13); // fallback location id for Editors
  });

  test('Create a new editor', async ({ page }) => {
    const userPage = new UserPage(page);
    await userPage.startCreatingUser(page, baseUrl, editorsLocationId, 'editor');
    await userPage.fillFirstName('EditorFirstName');
    await userPage.fillLastName('EditorLastName');
    await userPage.fillUsername('testeditor');
    await userPage.fillPassword('Test1234pw');
    await userPage.fillConfirmPassword('Test1234pw');
    await userPage.fillEmail('test@test.com');
    await userPage.setEnabled(true);
    await userPage.create();

    await userPage.assertOnContentViewFor('/Users/Editors/EditorFirstName EditorLastName');
    await userPage.assertContentAttributes([
      { label: 'First name', value: 'EditorFirstName' },
      { label: 'Last name', value: 'EditorLastName' },
      { label: 'Username', value: 'testeditor' },
      { label: 'Email', value: 'test@test.com' },
    ]);
  });

  test.describe('with fresh session', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test('User profile is accessible and can be edited', async ({ page }) => {
    const auth = new AuthPage(page);
    await auth.openLogin(baseUrl);
    await auth.login('testeditor', 'Test1234pw');
    await auth.assertOnDashboard();

    const profile = new UserProfilePage(page);
    await profile.goToUserProfile();
    await profile.assertOnUserProfilePage();
    await profile.editProfileSummary();

    const userPage = new UserPage(page);
    await userPage.fillFirstName('EditorFirstName2');
    await userPage.fillLastName('EditorLastName2');

    await profile.switchToFieldGroup('About');
    await profile.fillProfileField('job_title', 'TestJobTitle');
    await profile.fillProfileField('department', 'TestDepartment');
    await profile.fillProfileField('location', 'TestLocation');
    await userPage.update();

    await profile.assertOnUserProfilePage();
    await profile.assertProfileSummaryValues({
      fullName: 'EditorFirstName2 EditorLastName2',
      email: 'test@test.com',
      jobTitle: 'TestJobTitle',
      department: 'TestDepartment',
      location: 'TestLocation',
    });
    });
  });
});
