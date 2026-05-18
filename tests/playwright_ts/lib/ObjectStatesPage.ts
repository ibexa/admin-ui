import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class ObjectStatesPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openGroupsList(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/state/groups`);
  }

  async openGroup(name: string): Promise<void> {
    await this.findTableRowAcrossPages(name);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    await row.locator('a').first().click();
    await this.page.waitForLoadState('networkidle');
  }

  async openState(name: string): Promise<void> {
    await this.findTableRowAcrossPages(name);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    await row.locator('a').first().click();
    await this.page.waitForLoadState('networkidle');
  }

  async clickCreateGroup(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create a new Object State group' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' })).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async fillGroupForm(name: string, identifier?: string): Promise<void> {
    await this.fillField('Name', name);
    if (identifier) {
      await this.fillField('Identifier', identifier);
    }
  }

  async clickCreateState(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create a new Object State' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' }))
      .or(this.page.locator('a[href*="state/state/create"]'))
      .first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async fillStateForm(name: string, identifier?: string): Promise<void> {
    await this.fillField('Name', name);
    if (identifier) {
      await this.fillField('Identifier', identifier);
    }
  }

  async save(): Promise<void> {
    await this.clickContextButton('Save');
  }

  async discard(): Promise<void> {
    await this.clickContextButton('Discard');
  }

  async discardChanges(): Promise<void> {
    await this.clickContextButton('Discard changes');
  }

  async editGroupFromList(groupName: string): Promise<void> {
    await this.clickRowEditButton(groupName);
  }

  async editStateFromGroupPage(stateName: string): Promise<void> {
    await this.clickRowEditButton(stateName);
  }

  async startEditingFromDetailPage(): Promise<void> {
    const editBtn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Edit' }).first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.click({ force: true });
    await this.page.waitForLoadState('networkidle');
  }

  async assertOnGroupListPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/state\/groups/);
  }

  async assertOnGroupPage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertOnStatePage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertGroupInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertGroupNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertStateInGroup(stateName: string): Promise<void> {
    await this.assertTableRowPresent(stateName);
  }

  async assertStateNotInGroup(stateName: string): Promise<void> {
    await this.assertTableRowAbsent(stateName);
  }

  async assertGroupHasNoStates(): Promise<void> {
    await this.page.waitForLoadState('networkidle');
    // Check for an empty state message or that no state rows exist in the states table
    const emptyMsg = this.page.locator('.ibexa-empty-table-info, .ibexa-no-items, td.ibexa-table__cell--no-content');
    if (await emptyMsg.isVisible({ timeout: 3_000 }).catch(() => false)) {
      return;
    }
    // Fall back: count rows that look like object state entries (have an identifier/name column)
    const stateRows = this.page.locator('.ibexa-object-states .ibexa-table__row, #object-states .ibexa-table__row')
      .filter({ hasText: /\S/ });
    const count = await stateRows.count();
    expect(count).toBe(0);
  }

  async assertGroupAttributes(name: string, identifier: string): Promise<void> {
    await expect(this.page.getByText(name).first()).toBeVisible();
    await expect(this.page.getByText(identifier).first()).toBeVisible();
  }

  async assertStateAttributes(name: string, identifier: string): Promise<void> {
    await expect(this.page.getByText(name).first()).toBeVisible();
    await expect(this.page.getByText(identifier).first()).toBeVisible();
  }

  async deleteGroup(groupName: string): Promise<void> {
    await this.checkTableRow(groupName);
    const deleteBtn = this.page.locator('button:not(.ibexa-notifications-modal--delete--confirm)').filter({ hasText: 'Delete' }).first();
    await deleteBtn.click({ force: true });
    await this.page.waitForSelector('text=Do you want to delete', { timeout: 5_000 });
    await this.page.evaluate(() => {
      const allButtons = Array.from(document.querySelectorAll('button'));
      const cancelBtn = allButtons.find(b => (b as HTMLElement).offsetParent !== null && b.textContent?.trim() === 'Cancel');
      if (!cancelBtn) return;
      const container = cancelBtn.closest('dialog, [role="dialog"], .modal, .ibexa-modal');
      if (!container) return;
      const deleteBtn = Array.from(container.querySelectorAll('button')).find(b => b.textContent?.trim() === 'Delete');
      if (deleteBtn) (deleteBtn as HTMLElement).click();
    });
    await this.page.waitForLoadState('networkidle');
  }

  async deleteState(stateName: string): Promise<void> {
    await this.checkTableRow(stateName);
    const deleteBtn = this.page.locator('button:not(.ibexa-notifications-modal--delete--confirm)').filter({ hasText: 'Delete' }).first();
    await deleteBtn.click({ force: true });
    await this.page.waitForSelector('text=Do you want to delete', { timeout: 5_000 });
    await this.page.evaluate(() => {
      const allButtons = Array.from(document.querySelectorAll('button'));
      const cancelBtn = allButtons.find(b => (b as HTMLElement).offsetParent !== null && b.textContent?.trim() === 'Cancel');
      if (!cancelBtn) return;
      const container = cancelBtn.closest('dialog, [role="dialog"], .modal, .ibexa-modal');
      if (!container) return;
      const deleteBtn = Array.from(container.querySelectorAll('button')).find(b => b.textContent?.trim() === 'Delete');
      if (deleteBtn) (deleteBtn as HTMLElement).click();
    });
    await this.page.waitForLoadState('networkidle');
  }
}
