import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class UserPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  private fieldInput(fieldName: string): ReturnType<Page['locator']> {
    return this.page.locator(`input[id*="fieldsData_${fieldName}_value"], input[name*="[fieldsData][${fieldName}][value]"]`).first();
  }

  async fillFirstName(value: string): Promise<void> {
    const input = this.fieldInput('first_name');
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async fillLastName(value: string): Promise<void> {
    const input = this.fieldInput('last_name');
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async fillUsername(value: string): Promise<void> {
    const input = this.page.locator('input[id*="user_account_value_username"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await this.page.evaluate((val) => {
      const el = document.querySelector('input[id*="user_account_value_username"]') as HTMLInputElement | null;
      if (el) {
        el.removeAttribute('readonly');
        el.value = val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }, value);
  }

  async fillPassword(value: string): Promise<void> {
    const input = this.page.locator('input[id*="user_account_value_password_first"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async fillConfirmPassword(value: string): Promise<void> {
    const input = this.page.locator('input[id*="user_account_value_password_second"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async fillEmail(value: string): Promise<void> {
    const input = this.page.locator('input[id*="user_account_value_email"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(value);
  }

  async setEnabled(enabled: boolean): Promise<void> {
    const checkbox = this.page.locator('input[id*="user_account_value_enabled"]').first();
    await checkbox.waitFor({ state: 'visible', timeout: 10_000 });
    const isChecked = await checkbox.isChecked();
    if (enabled !== isChecked) {
      // The ibexa-toggle__indicator intercepts pointer events — click via JS
      await this.page.evaluate((id) => {
        const el = document.querySelector(`input[id*="${id}"]`) as HTMLInputElement | null;
        if (el) el.click();
      }, 'user_account_value_enabled');
    }
  }

  private async clickPublish(): Promise<void> {
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

  async create(): Promise<void> {
    await this.clickPublish();
  }

  async update(): Promise<void> {
    await this.clickPublish();
  }

  async startCreatingUser(page: Page, baseUrl: string, usersLocationId: number, contentTypeIdentifier: string = 'user'): Promise<void> {
    await page.goto(`${baseUrl}/admin/content/create/nodraft/${contentTypeIdentifier}/eng-GB/${usersLocationId}`);
    await page.waitForLoadState('networkidle');
  }

  async assertOnContentViewFor(path: string): Promise<void> {
    const titleEl = this.page.locator('.ibexa-page-title h1');
    await titleEl.waitFor({ state: 'visible', timeout: 10_000 });
    const parts = path.split('/');
    const itemName = parts[parts.length - 1];
    await expect(titleEl).toContainText(itemName);
  }

  async assertContentAttributes(attributes: Array<{ label: string; value: string }>): Promise<void> {
    for (const attr of attributes) {
      const valueEl = this.page.getByText(attr.value).first();
      await valueEl.waitFor({ state: 'visible', timeout: 10_000 });
      await expect(valueEl).toBeVisible();
    }
  }

  async performEditAction(): Promise<void> {
    // Try primary context menu buttons first
    const contextMenu = this.page.locator('.ibexa-context-menu');
    await contextMenu.waitFor({ state: 'visible', timeout: 10_000 });

    const primaryBtns = contextMenu.locator('.ibexa-context-menu__item:not(.ibexa-context-menu__item--more) .ibexa-btn');
    const count = await primaryBtns.count();
    let clicked = false;
    for (let i = 0; i < count; i++) {
      const btn = primaryBtns.nth(i);
      const text = (await btn.textContent() ?? '').trim();
      if (!text.includes('Edit')) continue;
      const box = await btn.boundingBox();
      if (!box) continue;
      const cx = box.x + box.width / 2;
      const cy = box.y + box.height / 2;
      const isOnTop = await this.page.evaluate(
        ([x, y, btnId]: [number, number, string]) => {
          const el = document.elementFromPoint(x, y);
          const target = document.getElementById(btnId) ?? document.querySelector(`[id="${btnId}"]`);
          return target ? target.contains(el) || el === target : false;
        },
        [cx, cy, await btn.getAttribute('id') ?? ''] as [number, number, string],
      );
      if (isOnTop) {
        await btn.click({ force: true });
        await this.page.waitForLoadState('networkidle');
        clicked = true;
        break;
      }
    }

    if (!clicked) {
      const moreBtn = contextMenu.locator('.ibexa-btn--more');
      await moreBtn.waitFor({ state: 'visible', timeout: 5_000 });
      await moreBtn.click();
      const popupItems = this.page.locator(
        '.ibexa-multilevel-popup-menu__branch:not(.ibexa-popup-menu--hidden) .ibexa-popup-menu__item:not(.ibexa-popup-menu__item--hidden) .ibexa-multilevel-popup-menu__item-content',
      );
      await popupItems.first().waitFor({ state: 'visible', timeout: 5_000 });
      const popupCount = await popupItems.count();
      for (let i = 0; i < popupCount; i++) {
        const item = popupItems.nth(i);
        const text = (await item.textContent() ?? '').trim();
        if (text.includes('Edit')) {
          await item.click();
          await this.page.waitForLoadState('networkidle');
          clicked = true;
          break;
        }
      }
    }

    if (!clicked) throw new Error("Edit action not found in context menu");

    // Handle language selection modal if present
    const confirmBtn = this.page.locator('.ibexa-extra-actions__confirm-btn');
    const visible = await confirmBtn.isVisible().catch(() => false);
    if (visible) {
      await confirmBtn.click();
      await this.page.waitForLoadState('networkidle');
    }
  }
}
