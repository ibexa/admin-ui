import React, { useContext, useEffect, useRef } from 'react';

import Tab from './components/tab/tab';
import Search from './components/search/search';

import { LoadedLocationsMapContext, MarkedLocationIdContext, TabsConfigContext } from './universal.discovery.module';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

const SearchTabModule = () => {
    const tabsConfig = useContext(TabsConfigContext);
    const restorationStateRef = useRef(null);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);

    const actionsDisabledMap = {
        'content-create-button': false,
        'sort-switcher': true,
        'view-switcher': true,
    };

    useEffect(() => {
        const isCleared = markedLocationId === null && loadedLocationsMap?.length === 0;

        if (!isCleared) {
            restorationStateRef.current = {
                markedLocationId,
                loadedLocationsMap,
            };
        }
    }, [setMarkedLocationId, dispatchLoadedLocationsAction, markedLocationId, loadedLocationsMap]);

    useEffect(() => {
        return () => {
            if (restorationStateRef.current) {
                setMarkedLocationId(restorationStateRef.current.markedLocationId);
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: restorationStateRef.current.loadedLocationsMap });
            }
        };
    }, []);

    return (
        <div className="m-search-tab">
            <Tab actionsDisabledMap={actionsDisabledMap} isRightSidebarHidden={true}>
                <Search itemsPerPage={tabsConfig.search.itemsPerPage} />
            </Tab>
        </div>
    );
};

const SearchTab = {
    id: 'search',
    component: SearchTabModule,
    getLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/*@Desc("Search")*/ 'search.label', {}, 'ibexa_universal_discovery_widget');
    },
    getIcon: () => getIconPath('search'),
    isHiddenOnList: true,
};

export { SearchTabModule as default, SearchTab };
