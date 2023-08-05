import React, { useContext, useEffect, useRef } from 'react';

import Tab from './components/tab/tab';
import Search from './components/search/search';

import {
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    TabsConfigContext
} from './universal.discovery.module';

const { ibexa, Translator } = window;

const SearchTabModule = () => {
    const tabsConfig = useContext(TabsConfigContext);
    const shouldRestorePreviousStateRef = useRef(true);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);

    const actionsDisabledMap = {
        'content-create-button': false,
        'sort-switcher': true,
        'view-switcher': true,
    };

    useEffect(() => {
        return () => {
            if (shouldRestorePreviousStateRef.current) {
                setMarkedLocationId(markedLocationId);
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: loadedLocationsMap });
            }
        };
    }, []);

    return (
        <div className="m-search-tab">
            <Tab actionsDisabledMap={actionsDisabledMap}>
                <Search itemsPerPage={tabsConfig.search.itemsPerPage} />
            </Tab>
        </div>
    );
};

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'search',
            component: SearchTabModule,
            label: Translator.trans(/*@Desc("Search")*/ 'search.label', {}, 'universal_discovery_widget'),
            icon: ibexa.helpers.icon.getIconPath('search'),
            isHiddenOnList: true,
        },
    ],
    true,
);

export default SearchTabModule;
