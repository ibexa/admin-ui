import { test } from '@playwright/test';
import { LanguagesPage } from '../lib/LanguagesPage';


test.describe('Languages management', { tag: ['@IbexaOSS', '@IbexaHeadless', '@IbexaExperience', '@IbexaCommerce'] }, () => {
  let languages: LanguagesPage;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    const lang = new LanguagesPage(page);
    await lang.openList();
    for (const name of ['Deutsch', 'Edited Deutsch']) {
      const row = page.locator('.ibexa-table__row').filter({ hasText: name });
      if (await row.count() > 0) {
        await lang.deleteLanguage(name);
      }
    }
    await page.close();
  });

  test.beforeEach(async ({ page }) => {
    languages = new LanguagesPage(page);
    await languages.openList();
  });

  test('Changes can be discarded while creating new Language', async () => {
    await languages.clickAddLanguage();
    await languages.fillLanguageForm('Deutsch', 'de-DE');
    await languages.discard();
    await languages.assertOnLanguageListPage();
    await languages.assertLanguageNotInList('Deutsch');
  });

  test('New Language can be added', async () => {
    await languages.clickAddLanguage();
    await languages.fillLanguageForm('Deutsch', 'de-DE');
    await languages.save();
    await languages.assertOnLanguagePage('Deutsch');
    await languages.assertLanguageAttributes({
      Name: 'Deutsch',
      'Language code': 'de-DE',
      Enabled: 'true',
    });
  });

  test('New Language with existing language code cannot be added', async () => {
    await languages.clickAddLanguage();
    await languages.fillLanguageForm('Deutsch Second', 'de-DE');
    await languages.save();
    await languages.assertErrorNotification('de-DE');
  });

  test('Changes can be discarded while editing Language', async () => {
    await languages.assertLanguageInList('Deutsch');
    await languages.editFromList('Deutsch');
    await languages.fillLanguageForm('Edited Deutsch');
    await languages.discardChanges();
    await languages.assertOnLanguageListPage();
    await languages.assertLanguageInList('Deutsch');
    await languages.assertLanguageNotInList('Edited Deutsch');
  });

  test('Language can be disabled', async () => {
    await languages.assertLanguageInList('Deutsch');
    await languages.editFromList('Deutsch');
    await languages.fillLanguageForm('Edited Deutsch');
    await languages.setEnabled(false);
    await languages.save();
    await languages.assertOnLanguagePage('Edited Deutsch');
    await languages.assertUpdateNotification('Edited Deutsch');
    await languages.assertLanguageAttributes({
      Name: 'Edited Deutsch',
      'Language code': 'de-DE',
      Enabled: 'false',
    });
  });

  test('Language can be enabled', async ({ page }) => {
    languages = new LanguagesPage(page);
    await languages.openList();
    await languages.assertLanguageInList('Edited Deutsch');
    await languages.editFromList('Edited Deutsch');
    await languages.setEnabled(true);
    await languages.save();
    await languages.assertOnLanguagePage('Edited Deutsch');
    await languages.assertUpdateNotification('Edited Deutsch');
    await languages.assertLanguageAttributes({
      Name: 'Edited Deutsch',
      'Language code': 'de-DE',
      Enabled: 'true',
    });
  });

  test('Language can be deleted', async () => {
    await languages.assertLanguageInList('Edited Deutsch');
    await languages.deleteLanguage('Edited Deutsch');
    await languages.assertDeleteNotification('Edited Deutsch');
    await languages.assertLanguageNotInList('Edited Deutsch');
  });
});
