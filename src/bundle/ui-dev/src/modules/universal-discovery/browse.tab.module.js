import React, { useContext } from 'react';

import Tab from './components/tab/tab';
import GridView from './components/grid-view/grid.view';
import Finder from './components/finder/finder';
import TreeView from './components/tree-view/tree.view';

import { CurrentViewContext, TabsConfigContext } from './universal.discovery.module';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

export const TAB_ID = 'browse';

const BrowseTabModule = () => {
    const [currentView] = useContext(CurrentViewContext);
    const tabsConfig = useContext(TabsConfigContext);
    const views = {
        grid: <GridView itemsPerPage={tabsConfig.browse.itemsPerPage} />,
        finder: <Finder itemsPerPage={tabsConfig.browse.itemsPerPage} />,
        tree: <TreeView itemsPerPage={tabsConfig.browse.itemsPerPage} />,
    };

    return (
        <div className="m-browse-tab">
            <Tab>{views[currentView]}</Tab>
        </div>
    );
};

export const BrowseTab = {
    id: TAB_ID,
    component: BrowseTabModule,
    getLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/*@Desc("Browse")*/ 'browse.label', {}, 'ibexa_universal_discovery_widget');
    },
    getIcon: () => getIconPath('browse'),
};

export default BrowseTabModule;
