import { BookmarksTab } from './bookmarks.tab.module';
import { BrowseTab, ImagePickerTab } from './browse.tab.module';
import { ContentCreateTab } from './content.create.tab.module';
import { ContentEditTab } from './content.edit.tab.module';
import { SearchTab } from './search.tab.module';

import { SortSwitcherMenuButton } from './components/sort-switcher/sort.switcher';
import { ContentCreateButtonMenuItem } from './components/content-create-button/content.create.button';
import { ViewSwitcherButton } from './components/view-switcher/view.switcher';

(function (ibexa) {
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [BookmarksTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [BrowseTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ImagePickerTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ContentCreateTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [ContentEditTab], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.tabs', [SearchTab], true);

    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [ContentCreateButtonMenuItem], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [SortSwitcherMenuButton], true);
    ibexa.addConfig('adminUiConfig.universalDiscoveryWidget.topMenuActions', [ViewSwitcherButton], true);
})(window.ibexa);
