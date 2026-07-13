import { Page, expect } from '@playwright/test';
import { AdminUiPage, UniversalDiscoveryWidget } from '@ibexa/cohesivo-playwright';

export class TrashPage extends AdminUiPage {
    readonly udw: UniversalDiscoveryWidget;

    constructor(page: Page) {
        super(page);
        this.udw = new UniversalDiscoveryWidget(page);
    }

    async open(): Promise<void> {
        await this.navigateTo(`/admin/trash/list`);
    }

    async assertNotEmpty(): Promise<void> {
        await expect(this.page.locator('.ibexa-table__row').first()).toBeVisible({ timeout: 10_000 });
    }

    async assertEmpty(): Promise<void> {
        const emptyEl = this.page.locator('.ibexa-table__empty-table-text')
            .or(this.page.getByText('Trash is empty'))
            .or(this.page.getByText('No items'));
        await expect(emptyEl.first()).toBeVisible({ timeout: 10_000 });
    }

    async emptyTrash(): Promise<void> {
        const emptyBtn = this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Empty Trash' })
            .or(this.page.locator('.ibexa-context-menu .ibexa-btn').filter({ hasText: 'Empty' })).first();
        await emptyBtn.click();
        await this.confirmDialogButton('Delete');
        await this.assertEmpty();
    }

    async assertItemInTrash(name: string): Promise<void> {
        await this.assertTableRowPresent(name);
    }

    async assertItemNotInTrash(name: string): Promise<void> {
        await this.assertTableRowAbsent(name);
    }

    async deleteFromTrash(items: string[]): Promise<void> {
        for (const item of items) {
            await this.checkTableRow(item);
        }
        const deleteBtn = this.page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' }).first();
        await deleteBtn.click();
        await this.confirmDialogButton('Delete');
    }

    async restoreFromTrash(items: string[]): Promise<void> {
        for (const item of items) {
            await this.checkTableRow(item);
        }
        // Find "Restore" button (not "Restore in a new location") by matching inner text exactly
        const restoreBtn = this.page.locator('button').filter({ hasNotText: 'in a new location' }).filter({ hasText: 'Restore' }).first();
        await restoreBtn.click();
    }

    /**
     * Restores the checked items under the location given by path (e.g. "Media/Files"),
     * driving the whole flow: restore button → UDW navigation → UDW confirm.
     */
    async restoreUnderNewLocation(items: string[], newLocationPath: string): Promise<void> {
        for (const item of items) {
            await this.checkTableRow(item);
        }
        const restoreBtn = this.page.locator('button').filter({ hasText: 'Restore in a new location' })
            .or(this.page.locator('button.ibexa-btn--open-udw')).first();
        await restoreBtn.click();

        await this.udw.selectPath(newLocationPath);
        await this.udw.confirm();
    }

    async searchInTrash(query: string): Promise<void> {
        const url = this.page.url().split('?')[0];
        await this.page.goto(`${url}?trash_search[content_name]=${encodeURIComponent(query)}`);
    }

    async filterByContentType(contentTypeName: string): Promise<void> {
        const select = this.page.locator('select').first();
        await select.selectOption({ label: contentTypeName });
    }
}
