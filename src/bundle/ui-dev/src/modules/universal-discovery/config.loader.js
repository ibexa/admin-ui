import UniversalDiscoveryModule from './universal.discovery.module';

import { BookmarksTab } from './bookmarks.tab.module';
import { BrowseTab } from './browse.tab.module';
import { ContentCreateTab } from './content.create.tab.module';
import { ContentEditTab } from './content.edit.tab.module';
import { SearchTab } from './search.tab.module';

import { SortSwitcherMenuButton } from './components/sort-switcher/sort.switcher';
import { ContentCreateButtonMenuItem } from './components/content-create-button/content.create.button';
import { ViewSwitcherButton } from './components/view-switcher/view.switcher';

import { SelectedItemEditMenuButton } from './components/content-edit-button/selected.item.edit.button';

import { TreeItemToggleSelectionMenuButton } from './components/tree-item-toggle-selection/tree.item.toggle.selection';

(function (ibexa) {
    ibexa.addConfig('modules.UniversalDiscovery', UniversalDiscoveryModule);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [BookmarksTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [BrowseTab], true);
    // ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ImagePickerTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ContentCreateTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ContentEditTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [SearchTab], true);

    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [ContentCreateButtonMenuItem], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [SortSwitcherMenuButton], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [ViewSwitcherButton], true);

    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.selectedItemActions', [SelectedItemEditMenuButton], true);

    ibexa.addConfig('adminUiConfig.contentTreeWidget.secondaryItemActions', [TreeItemToggleSelectionMenuButton], true);
})(window.ibexa);
