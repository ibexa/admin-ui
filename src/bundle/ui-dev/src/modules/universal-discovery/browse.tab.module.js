import React, { useContext } from 'react';

import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

import Tab from './components/tab/tab';
import GridView from './components/grid-view/grid.view';
import Finder from './components/finder/finder';
import TreeView from './components/tree-view/tree.view';

import { CurrentViewContext, TabsConfigContext } from './universal.discovery.module';
import { getTranslator } from '../modules.service';

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
    id: 'browse',
    component: BrowseTabModule,
    getLabel: () => getTranslator().trans(/*@Desc("Browse")*/ 'browse.label', {}, 'ibexa_universal_discovery_widget'),
    getIcon: () => getIconPath('browse'),
};

export const ImagePickerTab = {
    id: 'image_picker',
    priority: 10,
    component: BrowseTabModule,
    getLabel: () => getTranslator().trans(/*@Desc("Browse")*/ 'browse.label', {}, 'ibexa_universal_discovery_widget'),
    getIcon: () => getIconPath('browse'),
};

export default BrowseTabModule;
