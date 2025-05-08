import TableViewComponent from '../components/table-view/table.view.component.js';
import GridViewComponent from '../components/grid-view/grid.view.component.js';
import { VIEW_MODE_GRID, VIEW_MODE_TABLE } from '../constants.js';

const { Translator } = window;

const viewRegistry = new Map();

export const registerView = (viewName, { component, iconName, label }) => {
    viewRegistry.set(viewName, {
        component,
        switcherOption: {
            iconName,
            label,
            value: viewName,
        },
    });
};

registerView(VIEW_MODE_TABLE, {
    component: TableViewComponent,
    iconName: 'view-list',
    label: Translator.trans(/*@Desc("List view")*/ 'view_switcher.list_view', {}, 'ibexa_sub_items'),
});

registerView(VIEW_MODE_GRID, {
    component: GridViewComponent,
    iconName: 'view-grid',
    label: Translator.trans(/*@Desc("Grid view")*/ 'view_switcher.grid_view', {}, 'ibexa_sub_items'),
});

export const getViewComponent = (viewName) => viewRegistry.get(viewName)?.component;
export const getViewSwitcherOptions = () => Array.from(viewRegistry.values(), ({ switcherOption }) => switcherOption);
