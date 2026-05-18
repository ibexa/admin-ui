import { test, expect } from '@playwright/test';
import { RolesPage } from '../lib/RolesPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Roles management', () => {
  let roles: RolesPage;

  test.beforeAll(async () => {
    const api = new IbexaApiClient(baseUrl);
    await api.init();
    await api.deleteRoleByName('Test Role');
  });

  test('Changes can be discarded while creating Role', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openList(baseUrl);
    await roles.clickCreateRole();
    await roles.fillRoleName('Test Role');
    await roles.discard();
    await roles.assertOnRoleListPage();
    await roles.assertRoleNotInList('Test Role');
  });

  test('New Role can be created', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openList(baseUrl);
    await roles.clickCreateRole();
    await roles.fillRoleName('Test Role');
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesListEmpty();
    await roles.assertAssignmentsListEmpty();
  });

  test('Changes can be discarded while editing Role', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openList(baseUrl);
    await roles.assertRoleInList('Anonymous');
    await roles.editFromList('Anonymous');
    await roles.fillRoleName('Test Role');
    await roles.discardChanges();
    await roles.assertOnRoleListPage();
    await roles.assertRoleInList('Anonymous');
  });

  test('Role can be edited', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openList(baseUrl);
    await roles.assertRoleInList('Anonymous');
    await roles.editFromList('Anonymous');
    await roles.fillRoleName('Anonymous edited');
    await roles.save();
    await roles.assertOnRolePage('Anonymous edited');

    // Restore original name
    await roles.openList(baseUrl);
    await roles.editFromList('Anonymous edited');
    await roles.fillRoleName('Anonymous');
    await roles.save();
  });

  test('User assignation can be discarded', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickAssignUsersAndGroups();
    await roles.selectUsersViaUDW(['Users/Administrator users/Administrator User']);
    await roles.selectGroupsViaUDW(['Users/Editors', 'Users']);
    await roles.discard();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesListEmpty();
    await roles.assertAssignmentsListEmpty();
  });

  test('User can be assigned to role from the Roles list', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickAssignUsersAndGroups();
    await roles.selectUsersViaUDW([
      'Users/Anonymous users/Anonymous User',
      'Users/Administrator users/Administrator User',
    ]);
    await roles.selectGroupsViaUDW(['Users/Editors']);
    await roles.selectSubtreeLimitationViaUDW('Media/Images');
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertAssignmentsPresent([
      { 'User/Group': 'Administrator User', Limitation: 'Subtree: /Media/Images' },
      { 'User/Group': 'Anonymous User', Limitation: 'Subtree: /Media/Images' },
      { 'User/Group': 'Editors', Limitation: 'Subtree: /Media/Images' },
    ]);
  });

  test('User can be assigned to role from the Role details view', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickAssignUsersAndGroups();
    await roles.selectGroupsViaUDW(['Users']);
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertAssignmentsPresent([
      { 'User/Group': 'Administrator User', Limitation: 'Subtree: /Media/Images' },
      { 'User/Group': 'Editors', Limitation: 'Subtree: /Media/Images' },
      { 'User/Group': 'Anonymous User', Limitation: 'Subtree: /Media/Images' },
      { 'User/Group': 'Users', Limitation: 'None' },
    ]);
  });

  test('Assignment can be deleted from role', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.deleteAssignments(['Administrator User', 'Editors', 'Users']);
    await roles.assertOnRolePage('Test Role');
    await roles.assertAssignmentsPresent([
      { 'User/Group': 'Anonymous User', Limitation: 'Subtree: /Media/Images' },
    ]);
  });

  test('Adding policy can be discarded', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickCreatePolicy();
    await roles.selectPolicy('Content type / All functions');
    await roles.discard();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesListEmpty();
  });

  test('Policies can be added to role', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickCreatePolicy();
    await roles.selectPolicy('Content / Read');
    await roles.save();
    await roles.assertSuccessNotification('Now you can set Limitations for the Policy.');
    await roles.selectLimitation('Content type', ['File']);
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesPresent([{ policy: 'Content/Read', limitation: 'Content type: File' }]);
  });

  test('Policies without limitations can be added to role', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.clickCreatePolicy();
    await roles.selectPolicy('User / Password');
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesPresent([{ policy: 'User/Password', limitation: 'None' }]);
  });

  test('Policies can be edited', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.editPolicy('Content', 'Read');
    await roles.selectLimitation('Content type', ['Article', 'Folder']);
    await roles.selectSubtreeLimitationViaUDW('Users/Anonymous users');
    await roles.selectLimitation('State', ['Lock:Locked']);
    await roles.save();
    await roles.assertOnRolePage('Test Role');
    await roles.assertPoliciesPresent([
      { policy: 'Content/Read', limitation: 'Content type: Article, Folder' },
      { policy: 'Content/Read', limitation: 'Subtree: /Users/Anonymous users' },
      { policy: 'Content/Read', limitation: 'State: Lock:Locked' },
    ]);
  });

  test('Policy can be deleted', async ({ page }) => {
    roles = new RolesPage(page);
    await roles.openRolePage(baseUrl, 'Test Role');
    await roles.deletePolicies(['Content']);
    await roles.assertSuccessNotification("Removed Policies from Role 'Test Role'.");
    await roles.assertPoliciesListEmpty();
  });
});
