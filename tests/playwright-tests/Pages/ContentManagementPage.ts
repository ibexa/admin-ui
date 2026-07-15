import { Page, expect } from '@playwright/test';
import { AdminUiPage, UniversalDiscoveryWidget } from '@ibexa/cohesivo-playwright';

export class ContentManagementPage extends AdminUiPage {
    readonly udw: UniversalDiscoveryWidget;

    constructor(page: Page) {
        super(page);
        this.udw = new UniversalDiscoveryWidget(page);
    }

    async open(contentId: number, locationId: number): Promise<void> {
        await this.page.goto(`/admin/view/content/${contentId}/full/1/${locationId}`);
        await expect(this.page.locator('.ibexa-context-menu')).toBeVisible({ timeout: 20_000 });
    }

    /**
     * Clicks an action button in the context menu by its visible label text.
     * Handles both primary (visible) buttons and items hidden behind the "More" overflow button.
     */
    async performAction(label: string): Promise<void> {
        const contextMenu = this.page.locator('.ibexa-context-menu');
        await expect(contextMenu.locator('.ibexa-btn').first()).toBeVisible({ timeout: 10_000 });

        const primaryButton = contextMenu
            .locator('.ibexa-context-menu__item:not(.ibexa-context-menu__item--more) .ibexa-btn')
            .filter({ hasText: label })
            .first();

        // A primary button can be present in the DOM but covered by the "More" overflow.
        // Probe with elementFromPoint (read-only, no failed click attempt in the report)
        // and only then click for real, with Playwright's actionability checks intact.
        const isClickable = await primaryButton.count() > 0 && await primaryButton.evaluate((el) => {
            const box = el.getBoundingClientRect();
            const topmost = document.elementFromPoint(box.x + box.width / 2, box.y + box.height / 2);
            return topmost !== null && (el === topmost || el.contains(topmost));
        });

        if (isClickable) {
            await primaryButton.click();
            return;
        }

        await contextMenu.locator('.ibexa-btn--more').click();

        // The multilevel popup branch is appended to <body>; visibility is toggled
        // via the ibexa-popup-menu--hidden CSS class.
        const popupItem = this.page
            .locator('.ibexa-multilevel-popup-menu__branch:not(.ibexa-popup-menu--hidden) .ibexa-popup-menu__item:not(.ibexa-popup-menu__item--hidden)')
            .filter({ hasText: label })
            .first();
        await expect(popupItem, `Action '${label}' not found in context menu`).toBeVisible({ timeout: 5_000 });
        await popupItem.click();
    }

    async sendToTrash(): Promise<void> {
        await this.performAction('Send to trash');

        const modal = this.page.locator('#trash-location-modal, .ibexa-modal--trash-location').first();
        await expect(modal).toBeVisible({ timeout: 10_000 });

        // For items with children/relations the modal requires ticking confirmation
        // checkboxes before the submit button becomes enabled.
        const submitButton = modal.locator('.ibexa-btn--confirm-send-to-trash');
        if (await submitButton.isDisabled()) {
            const checkboxes = modal.locator('input[type="checkbox"]:not(:checked)');
            const count = await checkboxes.count();
            for (let i = 0; i < count; i++) {
                await checkboxes.nth(i).check({ force: true });
            }
        }

        await expect(submitButton).toBeEnabled({ timeout: 5_000 });
        await submitButton.click();
        await expect(modal).toBeHidden({ timeout: 10_000 });
    }

    async hide(): Promise<void> {
        await this.performAction('Hide');
        // "Hide" opens the "Schedule hiding" panel; confirm with the default "Hide now" option
        await expect(
            this.page.getByRole('heading', { name: 'Schedule hiding' }).filter({ visible: true }).first(),
        ).toBeVisible({ timeout: 10_000 });
        await this.page.getByRole('button', { name: 'Confirm' }).filter({ visible: true }).first().click();
    }

    async assertOnContentView(itemName: string): Promise<void> {
        await expect(this.page.locator('.ibexa-page-title h1')).toContainText(itemName, { timeout: 10_000 });
    }

    async assertSubitemPresent(name: string): Promise<void> {
        const subItems = this.page.locator('.m-sub-items');
        await expect(
            subItems.locator('.ibexa-table__row').filter({ hasText: name }).first(),
        ).toBeVisible({ timeout: 10_000 });
    }

    async assertSubitemAbsent(name: string): Promise<void> {
        // .m-sub-items is a React mount point — anchor on the rendered table
        // (or its empty state) before asserting absence, to avoid passing on a blank mount.
        const subItems = this.page.locator('.m-sub-items');
        await expect(
            subItems.locator('.ibexa-table, .ibexa-table__empty-table-text').first(),
        ).toBeVisible({ timeout: 10_000 });
        await expect(subItems.locator('.ibexa-table__row').filter({ hasText: name })).toHaveCount(0);
    }
}
