import { Page, Locator, expect } from '@playwright/test';
import { AdminUiPage, UniversalDiscoveryWidget, ContextMenu } from '@ibexa/cohesivo-playwright';

export class ContentManagementPage extends AdminUiPage {
    readonly udw: UniversalDiscoveryWidget;
    readonly contextMenu: ContextMenu;

    private readonly trashModal: Locator;
    private readonly trashModalSubmit: Locator;
    private readonly subItems: Locator;

    constructor(page: Page) {
        super(page);
        this.udw = new UniversalDiscoveryWidget(page);
        this.contextMenu = new ContextMenu(page);
        this.trashModal = page.locator('#trash-location-modal, .ibexa-modal--trash-location').first();
        this.trashModalSubmit = this.trashModal.locator('.ibexa-btn--confirm-send-to-trash');
        this.subItems = page.locator('.m-sub-items');
    }

    private subItemRow(name: string): Locator {
        return this.subItems.locator('.ibexa-table__row').filter({ hasText: name });
    }

    async open(contentId: number, locationId: number): Promise<void> {
        await this.page.goto(`/admin/view/content/${contentId}/full/1/${locationId}`);
        await this.contextMenu.expectVisible();
    }

    async sendToTrash(): Promise<void> {
        await this.contextMenu.clickAction('Send to trash');
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
        await this.contextMenu.clickAction('Hide');
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
