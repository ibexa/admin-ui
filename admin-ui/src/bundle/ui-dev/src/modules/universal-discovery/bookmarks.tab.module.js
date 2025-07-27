import React, { useContext, useState, useEffect, useRef } from 'react';

import Tab from './components/tab/tab';
import BookmarksList from './components/bookmarks-list/bookmarks.list';
import GridView from './components/grid-view/grid.view';
import Finder from './components/finder/finder';
import TreeView from './components/tree-view/tree.view';

import {
    CurrentViewContext,
    MarkedLocationIdContext,
    RestInfoContext,
    LoadedLocationsMapContext,
    SortingContext,
    SortOrderContext,
    RootLocationIdContext,
    TabsConfigContext,
} from './universal.discovery.module';
import { loadAccordionData } from './services/universal.discovery.service';

import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const BookmarksTabModule = () => {
    const isMarkedLocationSetByBookmarksRef = useRef(false);
    const restorationStateRef = useRef(null);
    const restInfo = useContext(RestInfoContext);
    const tabsConfig = useContext(TabsConfigContext);
    const [currentView] = useContext(CurrentViewContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [sorting] = useContext(SortingContext);
    const [sortOrder] = useContext(SortOrderContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [bookmarkedLocationMarked, setBookmarkedLocationMarked] = useState(null);
    const views = {
        grid: <GridView itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
        finder: <Finder itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
        tree: <TreeView itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
    };
    const renderBrowseLocations = () => {
        if (!markedLocationId) {
            return null;
        }

        return views[currentView];
    };

    useEffect(() => {
        const isCleared = markedLocationId === null && loadedLocationsMap?.length === 0;

        if (!isCleared && !isMarkedLocationSetByBookmarksRef.current) {
            restorationStateRef.current = {
                markedLocationId,
                loadedLocationsMap,
            };

            setMarkedLocationId(null);
            dispatchLoadedLocationsAction({ type: 'CLEAR_LOCATIONS' });
        }
    }, [setMarkedLocationId, dispatchLoadedLocationsAction, markedLocationId, loadedLocationsMap]);

    useEffect(() => {
        return () => {
            if (!isMarkedLocationSetByBookmarksRef.current) {
                setMarkedLocationId(restorationStateRef.current.markedLocationId);
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: restorationStateRef.current.loadedLocationsMap });
            }
        };
    }, []);

    useEffect(() => {
        if (!bookmarkedLocationMarked) {
            return;
        }

        isMarkedLocationSetByBookmarksRef.current = true;
        setMarkedLocationId(bookmarkedLocationMarked);

        loadAccordionData(
            {
                ...restInfo,
                parentLocationId: bookmarkedLocationMarked,
                sortClause: sorting,
                sortOrder: sortOrder,
                gridView: currentView === 'grid',
                rootLocationId,
            },
            (locationsMap) => {
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });
            },
        );
    }, [bookmarkedLocationMarked, currentView, restInfo, dispatchLoadedLocationsAction, setMarkedLocationId]);

    useEffect(() => {
        if (markedLocationId !== bookmarkedLocationMarked) {
            dispatchLoadedLocationsAction({ type: 'CUT_LOCATIONS', locationId: markedLocationId });
            setBookmarkedLocationMarked(null);
        }
    }, [markedLocationId, setBookmarkedLocationMarked, bookmarkedLocationMarked, dispatchLoadedLocationsAction]);

    return (
        <div className="m-bookmarks-tab">
            <Tab>
                {restorationStateRef.current && (
                    <>
                        <BookmarksList
                            itemsPerPage={tabsConfig.bookmarks.itemsPerPage}
                            setBookmarkedLocationMarked={setBookmarkedLocationMarked}
                        />
                        {renderBrowseLocations()}
                    </>
                )}
            </Tab>
        </div>
    );
};

export const BookmarksTab = {
    id: 'bookmarks',
    component: BookmarksTabModule,
    getLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/* @Desc("Bookmarks") */ 'bookmarks.label', {}, 'ibexa_universal_discovery_widget');
    },
    getIcon: () => getIconPath('bookmark'),
};

export default BookmarksTabModule;
