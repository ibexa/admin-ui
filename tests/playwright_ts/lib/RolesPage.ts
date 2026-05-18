import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class RolesPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async openList(baseUrl: string): Promise<void> {
    await this.page.goto(`${baseUrl}/admin/role/list`);
    await this.page.waitForLoadState('domcontentloaded');
  }

  async openRolePage(baseUrl: string, roleName: string): Promise<void> {
    await this.page.goto(`${baseUrl}/admin/role/list`);
    await this.page.waitForLoadState('domcontentloaded');
    await this.findTableRowAcrossPages(roleName);
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: roleName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const link = row.locator('a').first();
    await link.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  async clickCreateRole(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Create' })
      .or(this.page.locator('a[href*="/role/create"]')).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async fillRoleName(name: string): Promise<void> {
    const input = this.page.locator('#role_create_identifier, input[id*="_identifier"], input[name*="identifier"]').first();
    await input.waitFor({ state: 'visible', timeout: 10_000 });
    await input.fill(name);
  }

  async save(): Promise<void> {
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Save' }).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async discard(): Promise<void> {
    // On role creation form: button in .ibexa-context-menu; on assignment form: a link
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Discard' }).first();
    const link = this.page.locator('a').filter({ hasText: 'Discard' }).first();
    if (await btn.isVisible({ timeout: 2_000 }).catch(() => false)) {
      await btn.click({ force: true });
      await this.page.waitForLoadState('domcontentloaded');
    } else {
      await link.waitFor({ state: 'visible', timeout: 10_000 });
      await link.click({ force: true });
      await this.page.waitForLoadState('domcontentloaded');
      await this.page.waitForTimeout(1000);
    }
  }

  async discardChanges(): Promise<void> {
    await this.clickContextButton('Discard changes');
  }

  async editFromList(roleName: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: roleName }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const updateLink = row.locator('a[href*="/update"]').first();
    await updateLink.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async assertOnRoleListPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/role\/list/);
  }

  async assertOnRolePage(name: string): Promise<void> {
    await this.assertPageTitle(name);
  }

  async assertRoleInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertRoleNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async assertPoliciesListEmpty(): Promise<void> {
    await this.switchToTab('Policies');
    await this.page.locator('#ibexa-tab-policies').waitFor({ state: 'visible', timeout: 10_000 });
    const rows = this.page.locator('#ibexa-tab-policies tbody .ibexa-table__row:not(:has(.ibexa-table__empty-table-cell))');
    await expect(rows).toHaveCount(0);
  }

  async assertAssignmentsListEmpty(): Promise<void> {
    await this.switchToTab('Assignments');
    await this.page.locator('#ibexa-tab-users-and-groups').waitFor({ state: 'visible', timeout: 10_000 });
    const rows = this.page.locator('#ibexa-tab-users-and-groups tbody .ibexa-table__row:not(:has(.ibexa-table__empty-table-cell))');
    await expect(rows).toHaveCount(0);
  }

  async switchToTab(tabName: string): Promise<void> {
    const tab = this.page.locator('.nav-tabs .nav-link, .ibexa-tabs .nav-link').filter({ hasText: tabName }).first();
    await tab.waitFor({ state: 'attached', timeout: 10_000 });
    await tab.dispatchEvent('click');
    await this.page.waitForTimeout(500);
  }

  // Role assignments
  async clickAssignUsersAndGroups(): Promise<void> {
    await this.switchToTab('Assignments');
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Assign' })
      .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'users and groups' }))
      .or(this.page.locator('a[href*="/assignment/create"]')).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  private async navigateUDWAndSelect(paths: string[]): Promise<void> {
    for (const path of paths) {
      const segments = path.split('/');
      for (let i = 0; i < segments.length; i++) {
        const branchLocator = this.page.locator(`div.c-finder-branch:nth-of-type(${i + 1}) .c-finder-leaf`);
        await branchLocator.first().waitFor({ state: 'visible', timeout: 10_000 });
        const count = await branchLocator.count();
        for (let j = 0; j < count; j++) {
          const leaf = branchLocator.nth(j);
          const text = (await leaf.textContent() ?? '').trim();
          if (!text.includes(segments[i])) continue;
          if (i === segments.length - 1) {
            // Last segment: check the checkbox to select the item
            const checkbox = leaf.locator('input[type="checkbox"]').first();
            await this.page.evaluate((el) => (el as HTMLInputElement).click(), await checkbox.elementHandle());
          } else {
            // Intermediate segment: navigate into the tree
            await leaf.locator('.c-finder-leaf__name').click();
            const nextBranch = this.page.locator(`div.c-finder-branch:nth-of-type(${i + 2})`);
            await nextBranch.waitFor({ state: 'visible', timeout: 10_000 }).catch(() => {});
          }
          break;
        }
      }
    }
  }

  async selectUsersViaUDW(userPaths: string[]): Promise<void> {
    const usersUDWBtn = this.page.locator('button[data-universaldiscovery-title*="User"], .ibexa-assign__users button[id$="__btn"]').first();
    await usersUDWBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await usersUDWBtn.click();
    await this.page.waitForTimeout(500);

    await this.navigateUDWAndSelect(userPaths);

    const confirmBtn = this.page.locator('.c-actions-menu__confirm-btn');
    await confirmBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await confirmBtn.click({ force: true });
    // Wait for UDW to close
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 10_000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async selectGroupsViaUDW(groupPaths: string[]): Promise<void> {
    const groupsUDWBtn = this.page.locator('button[data-universaldiscovery-title*="Group"], .ibexa-assign__groups button[id$="__btn"]').first();
    await groupsUDWBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await groupsUDWBtn.click();
    await this.page.waitForTimeout(500);

    await this.navigateUDWAndSelect(groupPaths);

    const confirmBtn = this.page.locator('.c-actions-menu__confirm-btn');
    await confirmBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await confirmBtn.click({ force: true });
    // Wait for UDW to close
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 10_000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  async selectSubtreeLimitationViaUDW(path: string): Promise<void> {
    // Ensure no UDW is open from a previous step
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 5_000 }).catch(() => {});

    // Assignment form: click the Subtree radio via its label (has class ibexa-assign__limitations-item-radio)
    const subtreeLabel = this.page.locator('.ibexa-limitations__label').filter({ hasText: /^Subtree$/ }).first();
    if (await subtreeLabel.isVisible({ timeout: 2_000 }).catch(() => false)) {
      await subtreeLabel.click();
      await this.page.waitForTimeout(400);
    }

    // Assignment form: enable the container and click the select path button via JS
    const hasAssignmentForm = await this.page.locator('.ibexa-assign__limitations-item-subtree').isVisible({ timeout: 2_000 }).catch(() => false);
    if (hasAssignmentForm) {
      await this.page.evaluate(() => {
        // Remove disabled from the container so the button is clickable
        const container = document.querySelector('.ibexa-assign__limitations-item-subtree .ibexa-tag-view-select');
        if (container) container.removeAttribute('disabled');
        const btn = document.querySelector('.ibexa-assign__limitations-item-subtree .ibexa-tag-view-select__btn-select-path') as HTMLElement | null;
        if (btn) btn.click();
      });
    } else {
      // Policy limitation UDW button — pick the one whose data attribute references Subtree
      const policyBtn = this.page.locator('.ibexa-pick-location-limitation-button[data-location-input-selector*="Subtree"]')
        .or(this.page.locator('.ibexa-pick-location-limitation-button').first());
      await policyBtn.first().waitFor({ state: 'visible', timeout: 5_000 });
      await policyBtn.first().click();
    }
    await this.page.waitForTimeout(500);

    // Wait for UDW to open
    await this.page.locator('.m-ud').waitFor({ state: 'visible', timeout: 5_000 });
    // Ensure Browse tab is active
    await this.page.locator('.m-ud [title="Browse"], .m-ud .c-tab-selector__item').first().click().catch(() => {});
    await this.page.waitForTimeout(500);

    if (hasAssignmentForm) {
      // Assignment form 'single' config UDW — navigate by visible text nodes, leaves have no checkboxes
      await this.navigateSubtreeUDWAndSelect(path);
    } else {
      // Policy limitation 'multiple' config UDW — navigate finder tree with checkboxes
      await this.page.locator('.m-ud .c-finder-branch').first().waitFor({ state: 'visible', timeout: 10_000 });
      await this.navigateUDWAndSelect([path]);
    }

    const confirmBtn = this.page.locator('.c-actions-menu__confirm-btn');
    await confirmBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await confirmBtn.click({ force: true });
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 10_000 }).catch(() => {});
    await this.page.waitForTimeout(300);
  }

  private async navigateSubtreeUDWAndSelect(path: string): Promise<void> {
    const segments = path.split('/');
    const udw = this.page.locator('.m-ud');

    for (const segment of segments) {
      // Subtree UDW items are generic divs with direct text content (not inside .ibexa-instant-filter__)
      // Use JS to click the first visible text node matching the segment
      await this.page.waitForFunction(
        (text) => {
          const udwEl = document.querySelector('.m-ud');
          if (!udwEl) return false;
          const all = udwEl.querySelectorAll('*');
          for (const el of Array.from(all)) {
            if (el.classList.contains('ibexa-instant-filter__group-name')) continue;
            if ((el as HTMLElement).offsetParent === null) continue;
            if (el.childElementCount === 0 && el.textContent?.trim() === text) return true;
            const textNodes = Array.from(el.childNodes).filter(n => n.nodeType === 3 && n.textContent?.trim() === text);
            if (textNodes.length > 0 && (el as HTMLElement).offsetParent !== null) return true;
          }
          return false;
        },
        segment,
        { timeout: 10_000 },
      );

      await this.page.evaluate((text) => {
        const udwEl = document.querySelector('.m-ud');
        if (!udwEl) return;
        const all = udwEl.querySelectorAll('*');
        for (const el of Array.from(all)) {
          if (el.classList.contains('ibexa-instant-filter__group-name')) continue;
          if ((el as HTMLElement).offsetParent === null) continue;
          if (el.childElementCount === 0 && el.textContent?.trim() === text) {
            (el as HTMLElement).click(); return;
          }
          const textNodes = Array.from(el.childNodes).filter(n => n.nodeType === 3 && n.textContent?.trim() === text);
          if (textNodes.length > 0) { (el as HTMLElement).click(); return; }
        }
      }, segment);

      await this.page.waitForTimeout(500);
    }
  }

  async selectSectionLimitation(sectionName: string): Promise<void> {
    const select = this.page.locator('select').filter({ hasText: sectionName })
      .or(this.page.locator('select[name*="section"]')).first();
    await select.waitFor({ state: 'visible', timeout: 10_000 });
    await select.selectOption({ label: sectionName });
  }

  async assertAssignmentsPresent(assignments: Array<{ 'User/Group': string; Limitation: string }>): Promise<void> {
    await this.switchToTab('Assignments');
    // Wait for the Assignments tab pane to be active
    await this.page.locator('#ibexa-tab-users-and-groups').waitFor({ state: 'visible', timeout: 10_000 });
    for (const assignment of assignments) {
      const row = this.page.locator('#ibexa-tab-users-and-groups .ibexa-table__row').filter({ hasText: assignment['User/Group'] }).first();
      await row.waitFor({ state: 'visible', timeout: 10_000 });
      await expect(row).toBeVisible();
    }
  }

  async deleteAssignments(items: string[]): Promise<void> {
    await this.switchToTab('Assignments');
    const pane = this.page.locator('#ibexa-tab-users-and-groups');
    await pane.waitFor({ state: 'visible', timeout: 10_000 });
    for (const item of items) {
      const row = pane.locator('.ibexa-table__row').filter({ hasText: item }).first();
      await row.waitFor({ state: 'visible', timeout: 10_000 });
      const checkbox = row.locator('input[type="checkbox"]').first();
      await this.page.evaluate((el) => (el as HTMLInputElement).click(), await checkbox.elementHandle());
    }
    const unassignBtn = this.page.locator('#delete-role-assignments, button').filter({ hasText: 'Unassign' }).first();
    await unassignBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await unassignBtn.click({ force: true });
    await this.confirmDialogButton('Unassign');
    await this.page.waitForLoadState('domcontentloaded');
  }

  // Policy management
  async clickCreatePolicy(): Promise<void> {
    await this.switchToTab('Policies');
    await this.page.locator('#ibexa-tab-policies').waitFor({ state: 'visible', timeout: 10_000 });
    const btn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Add policy' })
      .or(this.page.locator('a[href*="/policy/create"]')).first();
    await btn.waitFor({ state: 'visible', timeout: 10_000 });
    await btn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }

  async selectPolicy(policyLabel: string): Promise<void> {
    // Policy select is a select widget
    const select = this.page.locator('select[name*="policy"]').first();
    await select.waitFor({ state: 'visible', timeout: 10_000 });
    // Policy label format: "Module / Function" → option value
    const [module, func] = policyLabel.split(' / ');
    const option = this.page.locator('option').filter({ hasText: policyLabel }).first();
    const value = await option.getAttribute('value') ?? '';
    await select.selectOption(value || { label: policyLabel });
  }

  async selectLimitation(limitationType: string, options: string[]): Promise<void> {
    const section = this.page.locator('.ibexa-update-policy__action-wrapper').filter({ hasText: limitationType }).first();
    await section.waitFor({ state: 'visible', timeout: 10_000 });
    const select = section.locator('select.ibexa-update-policy__source-input');
    await select.waitFor({ state: 'attached', timeout: 10_000 });
    // Select each option via JS to avoid custom widget interference
    for (const option of options) {
      await this.page.evaluate(
        ({ selectEl, optionLabel }) => {
          const el = selectEl as HTMLSelectElement;
          for (const opt of Array.from(el.options)) {
            if (opt.text.trim() === optionLabel || opt.value === optionLabel) {
              opt.selected = true;
              el.dispatchEvent(new Event('change', { bubbles: true }));
              break;
            }
          }
        },
        { selectEl: await select.elementHandle(), optionLabel: option },
      );
    }
    await this.page.waitForTimeout(300);
  }

  async assertPoliciesPresent(policies: Array<{ policy: string; limitation: string }>): Promise<void> {
    await this.switchToTab('Policies');
    const pane = this.page.locator('#ibexa-tab-policies');
    await pane.waitFor({ state: 'visible', timeout: 10_000 });
    for (const policy of policies) {
      const [module] = policy.policy.split('/');
      const row = pane.locator('.ibexa-table__row').filter({ hasText: module.trim() }).first();
      await row.waitFor({ state: 'visible', timeout: 10_000 });
      await expect(row).toBeVisible();
    }
  }

  async deletePolicies(modules: string[]): Promise<void> {
    await this.switchToTab('Policies');
    const pane = this.page.locator('#ibexa-tab-policies');
    await pane.waitFor({ state: 'visible', timeout: 10_000 });
    // Check the header checkbox to select all policies
    const headerCheckbox = pane.locator('thead input[type="checkbox"]').first();
    await headerCheckbox.waitFor({ state: 'visible', timeout: 10_000 });
    await this.page.evaluate((el) => (el as HTMLInputElement).click(), await headerCheckbox.elementHandle());
    await this.page.waitForTimeout(300);
    const deleteBtn = this.page.locator('#delete-policies').first();
    await deleteBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await deleteBtn.click({ force: true });
    await this.confirmDialogButton('Delete');
    await this.page.waitForLoadState('domcontentloaded');
  }

  async editPolicy(module: string, func: string): Promise<void> {
    await this.switchToTab('Policies');
    const pane = this.page.locator('#ibexa-tab-policies');
    await pane.waitFor({ state: 'visible', timeout: 10_000 });
    const row = pane.locator('.ibexa-table__row').filter({ hasText: module }).filter({ hasText: func }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('a.ibexa-btn--ghost, .ibexa-btn--no-text').first();
    await editBtn.click({ force: true });
    await this.page.waitForLoadState('domcontentloaded');
  }
}
