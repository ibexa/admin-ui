import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class TranslationPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async switchToTranslationsTab(): Promise<void> {
    const tab = this.page.locator('.ibexa-tabs .nav-link, [role="tab"]').filter({ hasText: 'Translations' }).first();
    await tab.waitFor({ state: 'visible', timeout: 10_000 });
    await tab.click({ force: true });
    await this.page.waitForTimeout(500);
  }

  async clickAddTranslation(): Promise<void> {
    const addBtn = this.page.locator('button, a').filter({ hasText: 'Add translation' }).first()
      .or(this.page.locator('#add-translation-modal-trigger, .ibexa-btn--add-translation')).first();
    await addBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await addBtn.click();
    await this.page.waitForTimeout(500);
  }

  async selectBaseTranslation(languageName: string): Promise<void> {
    const modal = this.page.locator('#add-translation-modal, .modal.show').first();
    const select = modal.locator('select[name*="base_language"], select').first();
    await select.waitFor({ state: 'visible', timeout: 10_000 });
    await select.selectOption({ label: languageName });
  }

  async selectNewLanguage(languageName: string): Promise<void> {
    const modal = this.page.locator('#add-translation-modal, .modal.show').first();
    const select = modal.locator('select[name*="language"], select').first();
    await select.waitFor({ state: 'visible', timeout: 10_000 });
    await select.selectOption({ label: languageName });
  }

  async confirmAddTranslation(): Promise<void> {
    const modal = this.page.locator('#add-translation-modal, .modal.show').first();
    const btn = modal.locator('.ibexa-btn--create-translation').first()
      .or(modal.locator('.modal-footer .ibexa-btn--primary, .modal-footer button[type="submit"]').first());
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await Promise.all([
      this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30_000 }),
      btn.click(),
    ]);
  }

  async addTranslationBasedOn(newLanguage: string, baseLanguage: string): Promise<void> {
    await this.clickAddTranslation();
    // Select new language in first dropdown
    const modal = this.page.locator('#add-translation-modal, .modal.show').first();
    await modal.waitFor({ state: 'visible', timeout: 10_000 });
    const selects = modal.locator('select');
    const count = await selects.count();
    if (count >= 2) {
      await selects.nth(1).selectOption({ label: newLanguage });
      await selects.nth(0).selectOption({ label: baseLanguage });
    } else {
      await selects.first().selectOption({ label: newLanguage });
    }
    await this.confirmAddTranslation();
  }

  async addTranslationWithoutBase(newLanguage: string): Promise<void> {
    await this.clickAddTranslation();
    const modal = this.page.locator('#add-translation-modal, .modal.show').first();
    await modal.waitFor({ state: 'visible', timeout: 10_000 });
    const selects = modal.locator('select');
    const count = await selects.count();
    const langSelect = count >= 2 ? selects.nth(1) : selects.first();
    await langSelect.selectOption({ label: newLanguage });
    await this.confirmAddTranslation();
  }

  async selectLanguagePreview(languageName: string): Promise<void> {
    const langSwitch = this.page.locator('.ibexa-language-switcher, [data-language-switcher]').first()
      .or(this.page.locator('select[name*="language"]').first());
    if (await langSwitch.isVisible()) {
      await langSwitch.selectOption({ label: languageName });
      await this.page.waitForLoadState('networkidle');
    } else {
      const link = this.page.locator('a, button').filter({ hasText: languageName }).first();
      await link.waitFor({ state: 'visible', timeout: 10_000 });
      await link.click();
      await this.page.waitForLoadState('networkidle');
    }
  }
}
