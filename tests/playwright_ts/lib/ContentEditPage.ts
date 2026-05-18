import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class ContentEditPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openCreate(baseUrl: string, contentTypeIdentifier: string, language: string, parentLocationId: number): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/content/create/nodraft/${contentTypeIdentifier}/${language}/${parentLocationId}`);
  }

  async fillTextField(fieldIdentifier: string, value: string): Promise<void> {
    const input = this.page.locator(`#ezplatform_content_forms_content_edit_fieldsData_${fieldIdentifier}_value`);
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async fillRichTextField(fieldIdentifier: string, value: string): Promise<void> {
    const fieldId = `ezplatform_content_forms_content_edit_fieldsData_${fieldIdentifier}_value`;
    const fieldContainer = this.page.locator(`.ibexa-field-edit:has(label[for="${fieldId}"])`).first();
    await fieldContainer.waitFor({ state: 'visible', timeout: 15_000 });
    // Wait for CKEditor to initialize (editable appears once CKEditor has mounted)
    await this.page.locator(`#${fieldId}__editable`).waitFor({ state: 'visible', timeout: 15_000 }).catch(() => {});
    // CKEditor5 keeps the textarea synced via change:data events but does NOT re-sync on form
    // submit. Setting the textarea directly (before submit) is therefore safe and reliable.
    // The ibexa xhtml5 edit format uses <p> elements (XHTML), not <para> (DocBook).
    await this.page.locator(`#${fieldId}`).evaluate((el, { text }) => {
      const textarea = el as HTMLTextAreaElement;
      const ns = textarea.value.match(/xmlns="([^"]+)"/)?.[1] ?? 'http://ibexa.co/namespaces/ezpublish5/xhtml5/edit';
      textarea.value = `<?xml version="1.0" encoding="UTF-8"?><section xmlns="${ns}"><p>${text}</p></section>`;
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }, { text: value });
  }

  async publish(): Promise<void> {
    await Promise.all([
      this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30_000 }),
      this.page.evaluate(() => {
        const btn = document.getElementById('ezplatform_content_forms_content_edit_publish') as HTMLButtonElement | null;
        if (btn) {
          btn.dataset.isFormValid = '1';
          btn.click();
        }
      }),
    ]);
  }

  async saveDraft(): Promise<void> {
    await Promise.all([
      this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30_000 }),
      this.page.evaluate(() => {
        const btn = document.getElementById('ezplatform_content_forms_content_edit_saveDraft') as HTMLButtonElement | null;
        if (btn) {
          btn.dataset.isFormValid = '1';
          btn.click();
        }
      }),
    ]);
  }

  async saveDraftAndClose(): Promise<void> {
    await Promise.all([
      this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30_000 }),
      this.page.evaluate(() => {
        const btn = document.getElementById('ezplatform_content_forms_content_edit_saveDraftAndClose') as HTMLButtonElement | null;
        if (btn) {
          btn.dataset.isFormValid = '1';
          btn.click();
        }
      }),
    ]);
  }

  async deleteDraft(): Promise<void> {
    await Promise.all([
      this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30_000 }),
      this.page.evaluate(() => {
        const btn = document.getElementById('ezplatform_content_forms_content_edit_cancel') as HTMLButtonElement | null;
        if (btn) {
          btn.dataset.isFormValid = '1';
          btn.click();
        }
      }),
    ]);
  }

  async preview(): Promise<void> {
    await this.clickContextButton('Preview');
  }

  async switchPreviewMode(mode: 'desktop' | 'tablet' | 'mobile'): Promise<void> {
    const btn = this.page.locator(`button[data-preview-mode="${mode}"]`);
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click();
    await this.page.waitForTimeout(500);
  }

  async goBackFromPreview(): Promise<void> {
    // Preview back button says "Close" and is in .ibexa-preview-header__item--back
    const backBtn = this.page.locator('.ibexa-preview-header__item--back a, .ibexa-preview-header__item--back button').first()
      .or(this.page.locator('a, button').filter({ hasText: /Close|Back to edit/i }).first());
    await backBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await backBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  async assertOnContentUpdatePage(title: string): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/content\/(edit|create)/);
    const heading = this.page.locator('.ibexa-edit-header__title, .ibexa-page-title h1').first();
    await heading.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(heading).toContainText(title);
  }

  async assertFieldValue(fieldIdentifier: string, value: string): Promise<void> {
    const input = this.page.locator(`#ezplatform_content_forms_content_edit_fieldsData_${fieldIdentifier}_value`);
    await expect(input).toHaveValue(value);
  }

  // Dashboard draft helpers
  async openDashboard(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/dashboard`);
  }

  async assertDraftOnDashboard(title: string): Promise<void> {
    // Dashboard Drafts table is React-rendered — wait for it to populate
    await this.page.waitForFunction(
      () => document.querySelectorAll('[role="tabpanel"] .ibexa-table__row').length > 0
        || document.querySelectorAll('.ibexa-table__row').length > 0,
      { timeout: 15_000 },
    ).catch(() => {});
    await this.assertTableRowPresent(title);
  }

  async assertNoDraftOnDashboard(title: string): Promise<void> {
    await this.page.waitForLoadState('networkidle');
    await this.page.waitForFunction(
      () => document.querySelectorAll('[role="tabpanel"] .ibexa-table__row').length > 0
        || document.querySelectorAll('.ibexa-table__row').length > 0,
      { timeout: 15_000 },
    ).catch(() => {});
    const rows = this.page.locator('.ibexa-table__row').filter({ hasText: title });
    await expect(rows).toHaveCount(0, { timeout: 10_000 });
  }

  async openDraftForEditing(title: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: title }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('a.ibexa-btn--ghost, a').first();
    await editBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  // Select new draft from conflict modal
  async startCreatingNewDraftFromConflictModal(): Promise<void> {
    const modal = this.page.locator('div#version-draft-conflict-modal, div.ibexa-modal--version-draft-conflict');
    await modal.waitFor({ state: 'visible', timeout: 15_000 });
    const addBtn = modal.locator('.ibexa-btn--add-draft');
    await addBtn.waitFor({ state: 'visible', timeout: 5_000 });
    await addBtn.click();
    await this.page.waitForLoadState('networkidle');
    // Wait for CKEditor to finish async initialization before the caller fills fields or publishes
    await this.page.locator('.ck-editor__editable, [contenteditable="true"]').first()
      .waitFor({ state: 'visible', timeout: 15_000 }).catch(() => {});
    await this.page.waitForTimeout(1_000);
  }

  async editDraftWithVersionFromConflictModal(versionNumber: number): Promise<void> {
    const modal = this.page.locator('div#version-draft-conflict-modal, div.ibexa-modal--version-draft-conflict');
    await modal.waitFor({ state: 'visible', timeout: 15_000 });
    // Find draft rows in the modal table
    const rows = modal.locator('.ibexa-table__row, tr:not(:has(th))');
    await rows.first().waitFor({ state: 'visible', timeout: 10_000 });
    const count = await rows.count();
    // Click edit link in the first available row (or the nth row)
    const idx = Math.min(versionNumber - 1, count - 1);
    const row = rows.nth(idx);
    const editLink = row.locator('a[href*="edit"], a[href*="draft"]').first();
    if (await editLink.count() > 0) {
      await editLink.click();
    } else {
      await row.locator('a, button').first().click();
    }
    await this.page.waitForLoadState('networkidle');
  }
}
