import { Page, expect } from '@playwright/test';
import { AdminUiPage } from './AdminUiPage';

export class ContentManagementPage extends AdminUiPage {
  constructor(page: Page) {
    super(page);
  }

  async open(baseUrl: string, contentId: number, locationId: number): Promise<void> {
    const cleanBase = baseUrl.replace(/\/$/, '');
    await this.page.goto(`${cleanBase}/admin/view/content/${contentId}/full/1/${locationId}`);
    await this.page.waitForLoadState('networkidle');
    // Wait for the React content tree (c-tb-* toolbox tree) to render initial items
    await this.page.locator('.c-tb-list-item-single').first()
      .waitFor({ state: 'attached', timeout: 20_000 }).catch(() => {});
    // Click "See more" to load additional items until the current item appears
    // (tree loads 30 items at a time; newly created items may not be in the first batch)
    for (let i = 0; i < 20; i++) {
      const active = await this.page.locator('.c-tb-list-item-single--active')
        .first().isVisible({ timeout: 500 }).catch(() => false);
      if (active) break;
      const loadMore = this.page.locator('.c-tb-list-item-single__load-more').first();
      const found = await loadMore.count() > 0;
      if (!found) break;
      await loadMore.click();
      await this.page.waitForTimeout(1_500);
    }
  }

  /**
   * Clicks an action button in the context menu by its visible label text.
   * Handles both primary (visible) buttons and items hidden behind the "More" overflow button.
   */
  async performAction(label: string): Promise<void> {
    const contextMenu = this.page.locator('.ibexa-context-menu');
    await contextMenu.waitFor({ state: 'visible', timeout: 10_000 });

    // Check primary buttons — only click ones not physically covered by the "More" overlay.
    // Use elementFromPoint to confirm the button is the topmost element at its center.
    const primaryButtons = contextMenu.locator(
      '.ibexa-context-menu__item:not(.ibexa-context-menu__item--more) .ibexa-btn',
    );
    const count = await primaryButtons.count();
    for (let i = 0; i < count; i++) {
      const btn = primaryButtons.nth(i);
      const text = (await btn.textContent() ?? '').trim();
      if (!text.includes(label)) continue;

      const box = await btn.boundingBox();
      if (!box) continue;

      const cx = box.x + box.width / 2;
      const cy = box.y + box.height / 2;

      // Check whether the button (or its child) is the topmost element at (cx, cy)
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
        return;
      }
    }

    // Fall back to the "More" overflow popup — trigger is the .ibexa-btn--more button, not the <li>
    const moreButton = contextMenu.locator('.ibexa-btn--more');
    await moreButton.waitFor({ state: 'visible', timeout: 5_000 });
    await moreButton.click();

    // The multilevel popup branch is appended to <body> by the JS.
    // Visibility is toggled via CSS class ibexa-popup-menu--hidden (not HTML hidden attr).
    const popupItems = this.page.locator(
      '.ibexa-multilevel-popup-menu__branch:not(.ibexa-popup-menu--hidden) .ibexa-popup-menu__item:not(.ibexa-popup-menu__item--hidden) .ibexa-multilevel-popup-menu__item-content',
    );
    await popupItems.first().waitFor({ state: 'visible', timeout: 5_000 });

    const popupCount = await popupItems.count();
    const popupTexts: string[] = [];
    for (let i = 0; i < popupCount; i++) {
      const item = popupItems.nth(i);
      const text = (await item.textContent() ?? '').trim();
      popupTexts.push(text);
      if (text.includes(label)) {
        await item.click();
        await this.page.waitForLoadState('networkidle');
        return;
      }
    }

    throw new Error(`Action button '${label}' not found in context menu`);
  }

  async sendToTrash(): Promise<void> {
    await this.performAction('Send to trash');

    // "Send to trash" always opens #trash-location-modal.
    // For items with children/relations a confirm checkbox is required to enable the button;
    // for empty items the button is already enabled — we just click it.
    const trashModal = this.page.locator('#trash-location-modal, .ibexa-modal--trash-location');
    const isModalVisible = await trashModal.waitFor({ state: 'visible', timeout: 5_000 })
      .then(() => true).catch(() => false);

    if (!isModalVisible) {
      // No modal — action was handled without confirmation
      await this.page.waitForLoadState('networkidle');
      return;
    }

    // Check all unchecked checkboxes in the modal (options + confirm checkbox)
    await this.page.evaluate(() => {
      const modal = document.querySelector('#trash-location-modal, .ibexa-modal--trash-location');
      if (!modal) return;
      modal.querySelectorAll<HTMLInputElement>('input[type="checkbox"]:not(:checked)')
        .forEach(cb => cb.click());
    });
    await this.page.waitForTimeout(300);

    // Click the submit button inside the modal
    const submitBtn = trashModal.locator('.ibexa-btn--confirm-send-to-trash');
    await submitBtn.waitFor({ state: 'visible', timeout: 5_000 });
    await submitBtn.click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Selects a content item in the UDW by navigating the tree path (e.g. "Media/Files").
   * Each path segment is a branch level in the UDW finder.
   */
  async selectInUDW(itemPath: string): Promise<void> {
    const udw = this.page.locator('.m-ud');
    await udw.waitFor({ state: 'visible', timeout: 10_000 });

    const segments = itemPath.split('/');
    for (let level = 1; level <= segments.length; level++) {
      const segmentName = segments[level - 1];
      const branchLocator = this.page.locator(`div.c-finder-branch:nth-of-type(${level}) .c-finder-leaf`);
      await branchLocator.first().waitFor({ state: 'visible', timeout: 10_000 });

      const leafCount = await branchLocator.count();
      let found = false;
      for (let i = 0; i < leafCount; i++) {
        const leaf = branchLocator.nth(i);
        const text = (await leaf.textContent() ?? '').trim();
        if (text.includes(segmentName)) {
          if (level < segments.length) {
            await leaf.locator('.c-finder-leaf__name').click();
          } else {
            // Last segment: check the checkbox to select (multiple-mode UDW)
            const checkbox = leaf.locator('input[type="checkbox"]');
            if (await checkbox.count() > 0) {
              await this.page.evaluate((el) => (el as HTMLInputElement).click(), await checkbox.elementHandle());
              await this.page.waitForTimeout(300);
            } else {
              await leaf.locator('.c-finder-leaf__name').click();
            }
          }
          found = true;
          break;
        }
      }

      if (!found) {
        console.warn(`UDW tree item '${segmentName}' not found at level ${level} — stopping navigation`);
        return;
      }

      if (level < segments.length) {
        // For intermediate nodes: wait for next branch to appear (navigation happened)
        const nextBranch = this.page.locator(`div.c-finder-branch:nth-of-type(${level + 1})`);
        await nextBranch.waitFor({ state: 'visible', timeout: 10_000 }).catch(() => {});
      }
    }
  }

  async confirmUDW(): Promise<void> {
    const confirmButton = this.page.locator('.c-actions-menu__confirm-btn');
    await confirmButton.waitFor({ state: 'visible', timeout: 10_000 });
    await confirmButton.click({ force: true });
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 10_000 }).catch(() => {});
    await this.page.waitForLoadState('domcontentloaded');
  }

  async closeUDW(): Promise<void> {
    const cancelButton = this.page.locator('.c-top-menu__cancel-btn');
    await cancelButton.waitFor({ state: 'visible', timeout: 10_000 });
    await cancelButton.click();
    await this.page.locator('.m-ud').waitFor({ state: 'hidden', timeout: 10_000 }).catch(() => {});
    await this.page.waitForLoadState('domcontentloaded');
  }

  async assertOnContentView(itemName: string): Promise<void> {
    const pageTitle = this.page.locator('.ibexa-page-title h1');
    await pageTitle.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(pageTitle).toContainText(itemName);
  }

  async assertSuccessNotification(text: string): Promise<void> {
    const notification = this.page.locator('.ibexa-notifications-container .ibexa-alert--success');
    await notification.waitFor({ state: 'visible', timeout: 10_000 });
    await expect(notification).toContainText(text);
  }

  async assertSubitemAbsent(name: string): Promise<void> {
    // .m-sub-items is a React mount point — wait for it to render rows inside
    const subItemsTable = this.page.locator('.m-sub-items');
    await subItemsTable.waitFor({ state: 'attached', timeout: 10_000 });
    await this.page.waitForFunction(
      (sel) => {
        const el = document.querySelector(sel);
        return el && el.querySelectorAll('.ibexa-table__row').length > 0;
      },
      '.m-sub-items',
      { timeout: 10_000 },
    );
    const items = subItemsTable.locator('.ibexa-table__row');
    const count = await items.count();
    for (let i = 0; i < count; i++) {
      const text = (await items.nth(i).textContent() ?? '').trim();
      expect(text).not.toContain(name);
    }
  }

  async hide(): Promise<void> {
    // Hide works by submitting form[name="content_visibility_update"] with visible=0.
    // We set the visibility field and submit the form directly.
    await this.page.locator('.ibexa-context-menu').waitFor({ state: 'visible', timeout: 10_000 });

    const submitted = await this.page.evaluate(() => {
      const form = document.querySelector('form[name="content_visibility_update"]') as HTMLFormElement | null;
      if (!form) return 'NO_FORM';
      const visField = form.querySelector('#content_visibility_update_visible') as HTMLInputElement | null;
      if (!visField) return 'NO_FIELD';
      visField.value = '0';
      form.submit();
      return 'OK';
    });

    if (submitted !== 'OK') {
      throw new Error(`Hide form issue: ${submitted}`);
    }

    await this.page.waitForLoadState('networkidle', { timeout: 15_000 }).catch(() => {});
  }

  async assertSubitemPresent(name: string): Promise<void> {
    const subItemsTable = this.page.locator('.m-sub-items');
    await subItemsTable.waitFor({ state: 'attached', timeout: 10_000 });
    const row = subItemsTable.locator('.ibexa-table__row').filter({ hasText: name }).first();
    await row.waitFor({ state: 'attached', timeout: 10_000 });
    await expect(row).toContainText(name);
  }
}
