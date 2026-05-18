import { test, expect } from '@playwright/test';
import { ContentEditPage } from '../lib/ContentEditPage';

const baseUrl = (process.env.APP_URL ?? 'http://behatplaywright50.lh').replace(/\/$/, '');

test.describe('Content Item preview', () => {
  test('Content can be previewed during creation', async ({ page }) => {
    const editor = new ContentEditPage(page);
    await editor.openCreate(baseUrl, 'folder', 'eng-GB', 2);
    await editor.fillTextField('name', 'Test Name');
    await editor.preview();

    await editor.switchPreviewMode('tablet');
    await editor.switchPreviewMode('mobile');
    await editor.switchPreviewMode('desktop');
    await editor.goBackFromPreview();

    await editor.assertOnContentUpdatePage('Test Name');
    await editor.assertFieldValue('name', 'Test Name');
  });
});
