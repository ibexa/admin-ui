import { test } from '@playwright/test';
import { SystemInfoPage } from '../lib/SystemInfoPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('System Information', () => {
  let systemInfo: SystemInfoPage;

  test.beforeEach(async ({ page }) => {
    systemInfo = new SystemInfoPage(page);
    await systemInfo.open(baseUrl);
  });

  test('Check Product Information', async () => {
    await systemInfo.goToTab('Product');
    await systemInfo.assertInfoTableVisible('Product');
  });

  test('Check Composer System Information', async () => {
    await systemInfo.goToTab('Composer');
    await systemInfo.assertInfoTableVisible('Composer');
    await systemInfo.assertPackageListed('ibexa/admin-ui');
    await systemInfo.assertPackageListed('ibexa/core');
  });

  test('Check Repository System Information', async () => {
    await systemInfo.goToTab('Repository');
    await systemInfo.assertInfoTableVisible('Repository');
  });

  test('Check Hardware System Information', async () => {
    await systemInfo.goToTab('Hardware');
    await systemInfo.assertInfoTableVisible('Hardware');
  });

  test('Check PHP System Information', async () => {
    await systemInfo.goToTab('PHP');
    await systemInfo.assertInfoTableVisible('PHP');
  });

  test('Check Symfony Kernel System Information', async () => {
    await systemInfo.goToTab('Symfony Kernel');
    await systemInfo.assertInfoTableVisible('Symfony Kernel');
    await systemInfo.assertBundleListed('IbexaAdminUiAssetsBundle');
    await systemInfo.assertBundleListed('IbexaAdminUiBundle');
    await systemInfo.assertBundleListed('IbexaCoreBundle');
  });

  test('Check services', async () => {
    await systemInfo.goToTab('Services');
    await systemInfo.assertInfoTableVisible('Services');
  });
});
