import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class ContentTypePage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openGroup(baseUrl: string, groupName: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/contenttypegroup/list`);
    const link = this.page.locator('.ibexa-table__row a').filter({ hasText: groupName }).first();
    await link.waitFor({ state: 'visible', timeout: 10_000 });
    await link.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  async clickCreateContentType(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' })
      .or(this.page.locator('a[href*="/contenttype/create"]')).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async fillName(name: string): Promise<void> {
    const input = this.page.getByLabel('Name', { exact: false }).first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(name);
  }

  async fillIdentifier(identifier: string): Promise<void> {
    const input = this.page.locator('input[name*="identifier"], #content_type_update_identifier').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(identifier);
  }

  async fillNamePattern(pattern: string): Promise<void> {
    // The name schema field may be inside a collapsed accordion — expand it first
    const toggle = this.page.locator('[data-bs-toggle="collapse"], .ibexa-accordion__header button, .accordion-button')
      .filter({ hasText: /Global properties|Name schema|Name pattern/i }).first();
    if (await toggle.isVisible({ timeout: 2_000 }).catch(() => false)) {
      const isCollapsed = await toggle.getAttribute('aria-expanded').catch(() => null);
      if (isCollapsed === 'false') {
        await toggle.click();
        await this.page.waitForTimeout(400);
      }
    }
    const input = this.page.locator('input[name*="nameSchema"], input[id*="nameSchema"], input[name*="urlAliasSchema"]').first();
    if (!await input.isVisible({ timeout: 2_000 }).catch(() => false)) {
      // Try scrolling down to find it
      await this.page.evaluate(() => window.scrollBy(0, 400));
      await this.page.waitForTimeout(200);
    }
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(pattern);
  }

  async selectCategory(categoryName: string): Promise<void> {
    const select = this.page.locator('select[name*="category"], select[id*="category"]').first();
    if (await select.isVisible({ timeout: 2_000 }).catch(() => false)) {
      await select.selectOption({ label: categoryName });
      return;
    }
    // Try checkbox via label
    const label = this.page.locator('label').filter({ hasText: categoryName }).first();
    if (await label.isVisible({ timeout: 2_000 }).catch(() => false)) {
      const forAttr = await label.getAttribute('for');
      if (forAttr) {
        const cb = this.page.locator(`#${forAttr}`);
        await this.page.evaluate((el) => (el as HTMLInputElement).click(), await cb.elementHandle());
        return;
      }
      await label.click();
      return;
    }
    // Try getByLabel
    const cb = this.page.getByLabel(categoryName, { exact: false }).first();
    if (await cb.isVisible({ timeout: 2_000 }).catch(() => false)) {
      await this.page.evaluate((el) => (el as HTMLInputElement).click(), await cb.elementHandle());
    }
  }

  async addField(fieldType: string): Promise<void> {
    // The field type sidebar is always visible on the create/edit form.
    // Clicking a field type item adds it to the active group (first group is auto-active).
    const fieldTypeItem = this.page.locator('.ibexa-available-field-type').filter({ hasText: fieldType }).first();
    await fieldTypeItem.waitFor({ state: 'visible', timeout: 15_000 });
    await fieldTypeItem.click();
    await this.page.waitForTimeout(800);
  }

  async setFieldName(fieldType: string, fieldName: string): Promise<void> {
    // Wait for the newly added field collapse to appear
    const fieldBlocks = this.page.locator('.ibexa-collapse--field-definition');
    await this.page.waitForFunction(
      () => document.querySelectorAll('.ibexa-collapse--field-definition').length > 0,
      { timeout: 10_000 },
    );
    const count = await fieldBlocks.count();
    const lastField = fieldBlocks.nth(count - 1);

    // Expand the collapse if it's collapsed
    const header = lastField.locator('.ibexa-collapse__header');
    const isExpanded = await lastField.locator('.ibexa-collapse__body').isVisible({ timeout: 1_000 }).catch(() => false);
    if (!isExpanded) {
      await header.click();
      await this.page.waitForTimeout(400);
    }

    const nameInput = lastField.locator('input[name*="[name]"], input[name*="[names]"]').first();
    await nameInput.waitFor({ state: 'visible', timeout: 10_000 });
    await nameInput.fill(fieldName);
  }

  async saveAndClose(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Save and close' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async discard(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Discard' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async assertOnGroupPage(groupName: string): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/contenttypegroup\/(\d+|list)/);
  }

  async assertContentTypeInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertContentTypeNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertOnContentTypePage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertGlobalProperties(properties: Array<{ label: string; value: string }>): Promise<void> {
    for (const prop of properties) {
      const el = this.page.getByText(prop.value).first();
      await el.waitFor({ state: 'visible', timeout: 10_000 });
      await expect(el).toBeVisible();
    }
  }

  async assertFieldPresent(fieldName: string): Promise<void> {
    const field = this.page.locator('.ibexa-table__row, .ibexa-field-definition-card').filter({ hasText: fieldName }).first();
    await field.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(field).toBeVisible();
  }
}
