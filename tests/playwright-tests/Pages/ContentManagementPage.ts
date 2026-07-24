import { Page, Locator, expect } from '@playwright/test';
import { AdminUiPage, UniversalDiscoveryWidget } from '@ibexa/cohesivo-playwright';

export class ContentManagementPage extends AdminUiPage {
    readonly udw: UniversalDiscoveryWidget;

    private readonly contextMenu: Locator;
    private readonly moreButton: Locator;
    private readonly trashModal: Locator;
    private readonly trashModalSubmit: Locator;
    private readonly subItems: Locator;

    constructor(page: Page) {
        super(page);
        this.udw = new UniversalDiscoveryWidget(page);
        this.contextMenu = page.locator('.ibexa-context-menu');
        this.moreButton = this.contextMenu.locator('.ibexa-btn--more');
        this.trashModal = page.locator('#trash-location-modal, .ibexa-modal--trash-location').first();
        this.trashModalSubmit = this.trashModal.locator('.ibexa-btn--confirm-send-to-trash');
        this.subItems = page.locator('.m-sub-items');
    }

    /** Primary (always-visible) context-menu action button by its label. */
    private primaryAction(label: string): Locator {
        return this.contextMenu
            .locator('.ibexa-context-menu__item:not(.ibexa-context-menu__item--more) .ibexa-btn')
            .filter({ hasText: label })
            .first();
    }

    /** Same action when it lives in the "More" overflow popup (appended to <body>). */
    private overflowAction(label: string): Locator {
        return this.page
            .locator('.ibexa-multilevel-popup-menu__branch:not(.ibexa-popup-menu--hidden) .ibexa-popup-menu__item:not(.ibexa-popup-menu__item--hidden)')
            .filter({ hasText: label })
            .first();
    }

    private subItemRow(name: string): Locator {
        return this.subItems.locator('.ibexa-table__row').filter({ hasText: name });
    }

    async open(contentId: number, locationId: number): Promise<void> {
        await this.page.goto(`/admin/view/content/${contentId}/full/1/${locationId}`);
        await expect(this.contextMenu).toBeVisible({ timeout: 20_000 });
    }

    /**
     * Clicks an action button in the context menu by its visible label text.
     * Handles both primary (visible) buttons and items hidden behind the "More" overflow button.
     */
    async performAction(label: string): Promise<void> {
        await expect(this.contextMenu.locator('.ibexa-btn').first()).toBeVisible({ timeout: 10_000 });

        const primaryButton = this.primaryAction(label);

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

        await this.moreButton.click();
        const item = this.overflowAction(label);
        await expect(item, `Action '${label}' not found in context menu`).toBeVisible({ timeout: 5_000 });
        await item.click();
    }

    async sendToTrash(): Promise<void> {
        await this.performAction('Send to trash');
        await expect(this.trashModal).toBeVisible({ timeout: 10_000 });

        // For items with children/relations the modal requires ticking confirmation
        // checkboxes before the submit button becomes enabled.
        if (await this.trashModalSubmit.isDisabled()) {
            const checkboxes = this.trashModal.locator('input[type="checkbox"]:not(:checked)');
            const count = await checkboxes.count();
            for (let i = 0; i < count; i++) {
                await checkboxes.nth(i).check({ force: true });
            }
        }

        await expect(this.trashModalSubmit).toBeEnabled({ timeout: 5_000 });
        await this.trashModalSubmit.click();
        await expect(this.trashModal).toBeHidden({ timeout: 10_000 });
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
        // reuse the shared page-title assertion from AdminUiPage
        await this.assertPageTitle(itemName);
    }

    async assertSubitemPresent(name: string): Promise<void> {
        await expect(this.subItemRow(name).first()).toBeVisible({ timeout: 10_000 });
    }

    async assertSubitemAbsent(name: string): Promise<void> {
        // .m-sub-items is a React mount point — anchor on the rendered table
        // (or its empty state) before asserting absence, to avoid passing on a blank mount.
        await expect(
            this.subItems.locator('.ibexa-table, .ibexa-table__empty-table-text').first(),
        ).toBeVisible({ timeout: 10_000 });
        await expect(this.subItemRow(name)).toHaveCount(0);
    }
}
