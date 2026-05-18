import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class BookmarkPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async open(baseUrl: string): Promise<void> {
    await this.navigateTo(`${baseUrl}/admin/bookmark/list`);
  }

  async bookmarkCurrentContent(): Promise<void> {
    const addBtn = this.page.locator('.ibexa-add-to-bookmarks__icon-wrapper--add');
    await addBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await addBtn.click();
    await this.page.waitForTimeout(500);
  }

  async removeBookmarkCurrentContent(): Promise<void> {
    const removeBtn = this.page.locator('.ibexa-add-to-bookmarks__icon-wrapper--remove');
    await removeBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await removeBtn.click();
    await this.page.waitForTimeout(500);
  }

  async assertIsBookmarked(): Promise<void> {
    const container = this.page.locator('.ibexa-add-to-bookmarks--checked');
    await container.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(container).toBeVisible();
  }

  async assertIsNotBookmarked(): Promise<void> {
    const container = this.page.locator('.ibexa-add-to-bookmarks');
    await container.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(container).not.toHaveClass(/ibexa-add-to-bookmarks--checked/);
  }

  async assertBookmarkInList(name: string): Promise<void> {
    await this.assertTableRowPresent(name);
  }

  async assertBookmarkNotInList(name: string): Promise<void> {
    await this.assertTableRowAbsent(name);
  }

  async navigateToBookmarkedContent(name: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const link = row.locator('a').first();
    await link.click();
    await this.page.waitForLoadState('networkidle');
  }

  async editBookmarkedContent(name: string): Promise<void> {
    const row = this.page.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'visible', timeout: 10_000 });
    const editBtn = row.locator('.ibexa-btn--content-edit, .ibexa-btn--ghost.ibexa-btn--no-text').first();
    await editBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  async deleteBookmark(name: string): Promise<void> {
    await this.checkTableRow(name);
    const removeBtn = this.page.locator('#bookmark_remove_remove, button').filter({ hasText: 'Remove' }).first();
    await removeBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await removeBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  // UDW-related bookmark methods

  /**
   * Scrolls the UDW finder branch to find an item by name, then marks it (shows meta preview).
   * The finder loads more items on scroll.
   */
  async scrollUDWFinderToItem(name: string): Promise<void> {
    // Wait for the first finder branch to render
    const branch = this.page.locator('.c-finder-branch__items-wrapper').first();
    await branch.waitFor({ state: 'attached', timeout: 10_000 });

    for (let i = 0; i < 20; i++) {
      const leaf = this.page.locator('.c-finder-leaf').filter({ hasText: name }).first();
      const isVisible = await leaf.isVisible({ timeout: 500 }).catch(() => false);
      if (isVisible) {
        await leaf.locator('.c-finder-leaf__name').click();
        await this.page.waitForTimeout(800);
        return;
      }
      // Scroll down to trigger loading more items
      await this.page.evaluate(() => {
        const w = document.querySelector('.c-finder-branch__items-wrapper');
        if (w) w.scrollTop = w.scrollHeight;
      });
      await this.page.waitForTimeout(800);
    }
    console.warn(`UDW finder item '${name}' not found after scrolling`);
  }

  async bookmarkInUDW(): Promise<void> {
    const addBtn = this.page.locator('.c-content-meta-preview__toggle-bookmark-button').filter({ hasText: 'Add to bookmarks' }).first();
    await addBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await addBtn.click();
    await this.page.waitForTimeout(500);
  }

  async assertIsBookmarkedInUDW(): Promise<void> {
    const removeBtn = this.page.locator('.c-content-meta-preview__toggle-bookmark-button').filter({ hasText: 'Remove from bookmarks' }).first();
    await removeBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(removeBtn).toBeVisible();
  }

  async switchUDWTab(tabName: string): Promise<void> {
    // Try multiple selectors to find the UDW tab button
    const tab = this.page.locator(
      `.c-tab-selector__item[title="${tabName}"], .c-tab-selector__item[aria-label="${tabName}"], [title="${tabName}"].c-tab-selector__item`,
    ).first();
    const genericTab = this.page.getByTitle(tabName).filter({ has: this.page.locator('.ibexa-icon') }).first();

    // Wait for either to be attached
    await Promise.race([
      tab.waitFor({ state: 'attached', timeout: 10_000 }).catch(() => {}),
      genericTab.waitFor({ state: 'attached', timeout: 10_000 }).catch(() => {}),
    ]);

    if (await tab.count() > 0) {
      await tab.click({ force: true });
    } else {
      await genericTab.click({ force: true });
    }
    await this.page.waitForTimeout(800);
  }

  async selectBookmarkedContentInUDW(name: string): Promise<void> {
    const item = this.page.locator('.c-bookmarks-list__item').filter({ hasText: name }).first();
    await item.waitFor({ state: 'visible', timeout: 10_000 });
    await item.locator('.c-bookmarks-list__item-name').click();
    await this.page.waitForTimeout(800);
  }

  async editSelectedContentFromUDW(): Promise<void> {
    const editBtn = this.page.locator('.c-content-edit-button__btn').first();
    await editBtn.waitFor({ state: 'visible', timeout: 10_000 });
    await editBtn.click();
    // Edit may open in an iframe (on-the-fly) OR navigate the full page
    await Promise.race([
      this.page.waitForURL(/\/admin\/content\/(edit|create)/, { timeout: 8_000 }).catch(() => {}),
      this.page.locator('.m-content-create__iframe, iframe').waitFor({ state: 'attached', timeout: 8_000 }).catch(() => {}),
    ]);
    await this.page.waitForTimeout(500);
  }
}
