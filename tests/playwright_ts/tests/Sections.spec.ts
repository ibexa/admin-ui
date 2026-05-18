import { test, expect } from '@playwright/test';
import { SectionsPage } from '../lib/SectionsPage';
import { ContentManagementPage } from '../lib/ContentManagementPage';
import { IbexaApiClient } from '../lib/IbexaApiClient';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Sections management', () => {
  let sections: SectionsPage;
  let api: IbexaApiClient;

  test.beforeAll(async () => {
    api = new IbexaApiClient(baseUrl);
    await api.init();
    await api.deleteSectionByName('Test Section');
    await api.deleteSectionByName('Test Section edited');
    await api.deleteSectionByName('Test Section edited2');
  });

  test.beforeEach(async ({ page }) => {
    sections = new SectionsPage(page);
    await sections.openList(baseUrl);
  });

  test('Changes can be discarded while creating new Section', async () => {
    await sections.clickCreateSection();
    await sections.fillSectionForm('Test Section', 'TestSectionIdentifier');
    await sections.discard();
    await sections.assertOnSectionListPage();
    await sections.assertSectionNotInList('Test Section');
  });

  test('New Section can be added', async () => {
    await sections.clickCreateSection();
    await sections.fillSectionForm('Test Section', 'TestSectionIdentifier');
    await sections.save();
    await sections.assertOnSectionPage('Test Section');
    await sections.assertSectionHasNoContentItems();
    await sections.assertSectionAttributes('Test Section', 'TestSectionIdentifier');
  });

  test('Content item assignation can be discarded', async ({ page }) => {
    sections = new SectionsPage(page);
    await api.createFolder('TestSection', 2);

    await sections.openList(baseUrl);
    await sections.assertSectionInList('Test Section');
    await sections.startAssigningFromList('Test Section');

    const contentPage = new ContentManagementPage(page);
    await contentPage.selectInUDW('root/TestSection');
    await contentPage.closeUDW();

    await sections.assertOnSectionListPage();
  });

  test('Content item can be assigned to section from the Sections list', async ({ page }) => {
    sections = new SectionsPage(page);
    const testSectionMediaId = await api.createFolder('TestSection', await api.getMainLocationId(
      await api.getContentIdByPath('Media'),
    ));

    await sections.openList(baseUrl);
    await sections.assertSectionInList('Test Section');
    await sections.startAssigningFromList('Test Section');

    const contentPage = new ContentManagementPage(page);
    await contentPage.selectInUDW('Media/TestSection');
    await contentPage.confirmUDW();

    await sections.assertSuccessNotification("1 Content items assigned to 'Test Section'");
    await sections.assertOnSectionPage('Test Section');
    await sections.assertContentItemsInSection([{ Name: 'TestSection' }]);
  });

  test('Changes can be discarded while editing Section', async () => {
    await sections.assertSectionInList('Test Section');
    await sections.editFromList('Test Section');
    await sections.fillSectionForm('Test Section edited');
    await sections.discardChanges();
    await sections.assertOnSectionListPage();
    await sections.assertSectionInList('Test Section');
    await sections.assertSectionNotInList('Test Section edited');
  });

  test('Section can be edited', async () => {
    await sections.assertSectionInList('Test Section');
    await sections.editFromList('Test Section');
    await sections.fillSectionForm('Test Section edited');
    await sections.save();
    await sections.assertOnSectionPage('Test Section edited');
    await sections.assertSuccessNotification('Test Section edited');
  });

  test('Changes can be discarded while editing Section from section details', async ({ page }) => {
    sections = new SectionsPage(page);
    await sections.openList(baseUrl);
    await sections.openSectionDetails('Test Section edited');

    await sections.startEditingFromDetailPage();
    await sections.fillSectionForm('Test Section edited2');
    await sections.discardChanges();
    await sections.assertOnSectionListPage();
    await sections.assertSectionInList('Test Section edited');
    await sections.assertSectionNotInList('Test Section edited2');
  });

  test('Section can be edited from section details', async ({ page }) => {
    sections = new SectionsPage(page);
    await sections.openList(baseUrl);
    await sections.openSectionDetails('Test Section edited');

    await sections.startEditingFromDetailPage();
    await sections.fillSectionForm('Test Section edited2');
    await sections.save();
    await sections.assertOnSectionPage('Test Section edited2');
    await sections.assertSuccessNotification('Test Section edited2');
  });

  test('Non-empty section cannot be deleted', async () => {
    await sections.assertSectionInList('Test Section edited2');
    await sections.assertSectionHasAssignedItems('Test Section edited2');
  });

  test('Content item can be reassigned to section from the Sections details', async ({ page }) => {
    sections = new SectionsPage(page);
    await sections.openList(baseUrl);
    await sections.clickCreateSection();
    await sections.fillSectionForm('TestSectionAssign', 'TestSectionAssignIdentifier');
    await sections.save();

    await page.goto(`${baseUrl}/admin/section/list`);
    await page.waitForLoadState('domcontentloaded');
    const sectionLink = page.locator('a').filter({ hasText: 'TestSectionAssign' }).first();
    await sectionLink.click();
    await page.waitForLoadState('domcontentloaded');

    await sections.startAssigningFromDetailPage();
    const contentPage = new ContentManagementPage(page);
    await contentPage.selectInUDW('Media/TestSection');
    await contentPage.confirmUDW();

    await sections.assertSuccessNotification("1 Content items assigned to 'TestSectionAssign'");
    await sections.assertOnSectionPage('TestSectionAssign');
    await sections.assertContentItemsInSection([{ Name: 'TestSection' }]);
  });

  test('Empty section can be deleted', async () => {
    await sections.assertSectionInList('Test Section edited2');
    await sections.assertSectionHasNoAssignedItems('Test Section edited2');
    await sections.deleteSection('Test Section edited2');
    await sections.assertSuccessNotification('Test Section edited2');
    await sections.assertSectionNotInList('Test Section edited2');
  });

  test('Section can be deleted from section details', async ({ page }) => {
    sections = new SectionsPage(page);
    await sections.openList(baseUrl);
    await sections.clickCreateSection();
    await sections.fillSectionForm('Test Section', 'TestSectionIdentifier2');
    await sections.save();
    await sections.assertSuccessNotification('Test Section');

    await sections.deleteSectionFromDetailPage();
    await sections.assertSuccessNotification('Test Section');
    await sections.assertSectionNotInList('Test Section');
  });
});
