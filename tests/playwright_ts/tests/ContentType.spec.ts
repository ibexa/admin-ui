import { test, expect } from '@playwright/test';
import { ContentTypePage } from '../lib/ContentTypePage';


test.describe('Content types management', { tag: ['@IbexaOSS', '@IbexaHeadless', '@IbexaExperience', '@IbexaCommerce'] }, () => {
  test('Changes can be discarded while creating content type', async ({ page }) => {
    const ct = new ContentTypePage(page);
    await ct.openGroup('Content');
    await ct.clickCreateContentType();
    await ct.fillName('Test content type');
    await ct.fillIdentifier('TestContentTypeIdentifier');
    await ct.discard();
    await ct.assertOnGroupPage('Content');
    await ct.assertContentTypeNotInList('Test content type');
  });

  test('New content type can be added to content type group', async ({ page }) => {
    const ct = new ContentTypePage(page);
    await ct.openGroup('Content');
    await ct.clickCreateContentType();
    await ct.fillName('Test content type');
    await ct.fillIdentifier('TestContentTypeIdentifier');
    await ct.fillNamePattern('<name>');
    await ct.selectCategory('Content');
    await ct.addField('Country');
    await ct.setFieldName('Country', 'Country field');
    await ct.saveAndClose();
    await ct.assertOnContentTypePage('Test content type');
    await ct.assertGlobalProperties([
      { label: 'Name', value: 'Test content type' },
      { label: 'Identifier', value: 'TestContentTypeIdentifier' },
    ]);
    await ct.assertFieldPresent('Country field');
  });
});
