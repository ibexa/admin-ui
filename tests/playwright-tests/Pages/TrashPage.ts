import { Page, Locator, expect } from '@playwright/test';
import { AdminUiPage, UniversalDiscoveryWidget, ContextMenu } from '@ibexa/cohesivo-playwright';

export class TrashPage extends AdminUiPage {
    readonly udw: UniversalDiscoveryWidget;
    readonly contextMenu: ContextMenu;

    private readonly firstRow: Locator;
    private readonly emptyState: Locator;
    private readonly searchInput: Locator;
    private readonly restoreButton: Locator;
    private readonly restoreUnderNewLocationButton: Locator;
    private readonly bulkDeleteButton: Locator;

    constructor(page: Page) {
        super(page);
        this.udw = new UniversalDiscoveryWidget(page);
        this.contextMenu = new ContextMenu(page);

        this.firstRow = page.locator('.ibexa-table__row').first();
        this.emptyState = page.locator('.ibexa-table__empty-table-text')
            .or(page.getByText('Trash is empty'))
            .or(page.getByText('No items'));
        this.searchInput = page.locator('input[name="trash_search[content_name]"]');
        this.restoreButton = page.getByRole('button', { name: 'Restore', exact: true });
        this.restoreUnderNewLocationButton = page.getByRole('button', { name: 'Restore in a new location' })
            .or(page.locator('button.ibexa-btn--open-udw')).first();
        // toolbar Delete only — [data-bs-dismiss] excludes the modal's own dismiss button
        this.bulkDeleteButton = page.locator('button:not([data-bs-dismiss])').filter({ hasText: 'Delete' }).first();
    }

    async open(): Promise<void> {
        await this.navigateTo(`/admin/trash/list`);
    }

    async assertNotEmpty(): Promise<void> {
        await expect(this.firstRow).toBeVisible({ timeout: 10_000 });
    }

    async assertEmpty(): Promise<void> {
        await expect(this.emptyState.first()).toBeVisible({ timeout: 10_000 });
    }

    async emptyTrash(): Promise<void> {
        await this.contextMenu.clickAction(/Empty( Trash)?/);
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
        await this.bulkDeleteButton.click();
        await this.confirmDialogButton('Delete');
    }

    async restoreFromTrash(items: string[]): Promise<void> {
        for (const item of items) {
            await this.checkTableRow(item);
        }
        await this.restoreButton.click();
    }

    /**
     * Restores the checked items under the location given by path (e.g. "Media/Files"),
     * driving the whole flow: restore button → UDW navigation → UDW confirm.
     */
    async restoreUnderNewLocation(items: string[], newLocationPath: string): Promise<void> {
        for (const item of items) {
            await this.checkTableRow(item);
        }
        await this.restoreUnderNewLocationButton.click();
        await this.udw.selectPath(newLocationPath);
        await this.udw.confirm();
    }

    async searchInTrash(query: string): Promise<void> {
        // Drive the search form as a user would: type the query and submit.
        // The form is a plain GET, so pressing Enter navigates to the filtered list.
        await this.searchInput.fill(query);
        await this.searchInput.press('Enter');
        await this.page.waitForLoadState('networkidle');
    }

    async filterByContentType(contentTypeName: string): Promise<void> {
        await this.page.locator('select').first().selectOption({ label: contentTypeName });
    }
}
