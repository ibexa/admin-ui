import React, { useContext } from 'react';

import Tab from './components/tab/tab';
import GridView from './components/grid-view/grid.view';
import Finder from './components/finder/finder';
import TreeView from './components/tree-view/tree.view';

import { CurrentViewContext, TabsConfigContext } from './universal.discovery.module';

const { Translator, ibexa } = window;

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

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'browse',
            component: BrowseTabModule,
            label: Translator.trans(/*@Desc("Browse")*/ 'browse.label', {}, 'universal_discovery_widget'),
            icon: window.ibexa.helpers.icon.getIconPath('browse'),
        },
    ],
    true,
);

export default BrowseTabModule;
