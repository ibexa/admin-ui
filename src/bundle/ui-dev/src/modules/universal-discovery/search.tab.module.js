import React, { useContext, useEffect } from 'react';

import Tab from './components/tab/tab';
import Search from './components/search/search';

import { LoadedLocationsMapContext, MarkedLocationIdContext, TabsConfigContext } from './universal.discovery.module';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

const SearchTabModule = () => {
    const tabsConfig = useContext(TabsConfigContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);

    const actionsDisabledMap = {
        'content-create-button': false,
        'sort-switcher': true,
        'view-switcher': true,
    };

    useEffect(() => {
        return () => {
            setMarkedLocationId(markedLocationId);
            dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: loadedLocationsMap });
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

const SearchTab = {
    id: 'search',
    component: SearchTabModule,
    getLabel: () => getTranslator().trans(/*@Desc("Search")*/ 'search.label', {}, 'ibexa_universal_discovery_widget'),
    getIcon: () => getIconPath('search'),
    isHiddenOnList: true,
};

export { SearchTabModule as ValueTypeDefault, SearchTab };
